#!/bin/bash
# =============================================================================
# Entrypoint container — MTsN 2 Kota Malang
#
# Peran ditentukan env CONTAINER_ROLE:
#   app        → siapkan storage, tunggu database, migrate, seed (sekali),
#                cache config/route/view, lalu jalankan nginx + php-fpm.
#   worker     → tunggu app siap, lalu jalankan queue:work.
#   scheduler  → tunggu app siap, lalu jalankan schedule:work.
#
# Semua perintah artisan dijalankan sebagai www-data agar file yang dibuat
# (log, cache, backup) tidak dimiliki root dan tetap bisa ditulis php-fpm.
# =============================================================================
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/var/www/html}"
ROLE="${CONTAINER_ROLE:-app}"
APP_USER="${APP_USER:-www-data}"
KEY_FILE="$APP_DIR/storage/app/private/.app_key"

cd "$APP_DIR"

log()  { echo "[entrypoint:$ROLE] $*"; }
warn() { echo "[entrypoint:$ROLE] PERINGATAN: $*" >&2; }
fail() { echo "[entrypoint:$ROLE] GAGAL: $*" >&2; exit 1; }

# Jalankan perintah sebagai www-data (atau langsung bila sudah bukan root).
as_app() {
    if [ "$(id -u)" = "0" ]; then
        setpriv --reuid="$APP_USER" --regid="$APP_USER" --init-groups -- env HOME=/tmp "$@"
    else
        "$@"
    fi
}

artisan() { as_app php artisan "$@" --no-interaction; }

# -----------------------------------------------------------------------------
# 1. Struktur folder storage (volume baru bisa kosong)
# -----------------------------------------------------------------------------
prepare_storage() {
    mkdir -p \
        storage/app/public storage/app/private storage/app/backups \
        storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/framework/testing \
        storage/logs bootstrap/cache

    if [ "$(id -u)" = "0" ]; then
        # Hanya ubah pemilik bila perlu (volume besar → chown -R lambat)
        if [ "$(stat -c %U storage)" != "$APP_USER" ] || [ -n "$(find storage bootstrap/cache -maxdepth 3 ! -user "$APP_USER" -print -quit 2>/dev/null)" ]; then
            log "Menyesuaikan kepemilikan storage ke $APP_USER..."
            chown -R "$APP_USER:$APP_USER" storage bootstrap/cache
        fi
    fi

    # Symlink public/storage → storage/app/public (sudah ada di image; jaga-jaga)
    if [ ! -L public/storage ]; then
        ln -sfn "$APP_DIR/storage/app/public" public/storage
    fi
}

# -----------------------------------------------------------------------------
# 2. APP_KEY — wajib stabil (mengenkripsi sesi & rahasia di Pengaturan)
# -----------------------------------------------------------------------------
ensure_app_key() {
    if [ -n "${APP_KEY:-}" ]; then
        return
    fi

    if [ -s "$KEY_FILE" ]; then
        APP_KEY="$(cat "$KEY_FILE")"
        export APP_KEY
        warn "APP_KEY tidak diisi di Coolify; memakai kunci tersimpan di volume ($KEY_FILE)."
        return
    fi

    if [ "$ROLE" != "app" ]; then
        # worker/scheduler menunggu app membuat kunci (lihat wait_for_app)
        return
    fi

    APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    export APP_KEY
    umask 077
    printf '%s' "$APP_KEY" > "$KEY_FILE"
    chown "$APP_USER:$APP_USER" "$KEY_FILE" 2>/dev/null || true
    umask 022
    warn "APP_KEY belum diisi — kunci baru dibuat dan disimpan di volume."
    warn "SALIN nilai berikut ke Environment Variables APP_KEY di Coolify agar aman:"
    warn "APP_KEY=$APP_KEY"
}

# -----------------------------------------------------------------------------
# 3. Tunggu database siap
# -----------------------------------------------------------------------------
wait_for_database() {
    if [ "${DB_CONNECTION:-pgsql}" != "pgsql" ]; then
        return
    fi

    local host="${DB_HOST:-db}" port="${DB_PORT:-5432}" timeout="${DB_WAIT_TIMEOUT:-120}"
    log "Menunggu PostgreSQL di $host:$port (maks ${timeout} detik)..."

    local waited=0
    until pg_isready -h "$host" -p "$port" -U "${DB_USERNAME:-postgres}" -d "${DB_DATABASE:-postgres}" -q; do
        waited=$((waited + 2))
        if [ "$waited" -ge "$timeout" ]; then
            fail "Database tidak bisa dihubungi setelah ${timeout} detik. Periksa DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD."
        fi
        sleep 2
    done
    log "Database siap."
}

# -----------------------------------------------------------------------------
# 4. Peran app: migrasi, seed awal, cache
# -----------------------------------------------------------------------------
run_migrations() {
    if [ "${AUTO_MIGRATE:-true}" != "true" ]; then
        log "AUTO_MIGRATE=false — migrasi dilewati."
        return
    fi

    log "Menjalankan migrasi database..."
    # --isolated: aman bila ada lebih dari satu container app yang start
    # bersamaan. Lock memakai cache FILE (volume bersama) karena tabel cache
    # database belum ada pada instalasi baru.
    CACHE_STORE=file artisan migrate --force --isolated
}

seed_if_empty() {
    if [ "${AUTO_SEED:-true}" != "true" ]; then
        return
    fi

    local users
    users="$(as_app php -r '
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo Illuminate\Support\Facades\Schema::hasTable("users") ? App\Models\User::withTrashed()->count() : -1;
    ' 2>/dev/null || echo "-1")"

    if [ "$users" = "0" ]; then
        log "Database baru (belum ada pengguna) — menjalankan seeder awal..."
        artisan db:seed --force
        log "Seeder selesai. Password super admin tertulis di atas (atau pakai ADMIN_DEFAULT_PASSWORD)."
    fi
}

optimize_app() {
    log "Menyusun cache konfigurasi, route, view, dan event..."
    artisan optimize:clear >/dev/null
    artisan config:cache
    artisan route:cache
    artisan view:cache
    artisan event:cache
    artisan filament:optimize >/dev/null 2>&1 || true
}

# -----------------------------------------------------------------------------
# 5. worker/scheduler: tunggu app selesai migrasi (hindari query ke tabel
#    yang belum ada saat deploy pertama)
# -----------------------------------------------------------------------------
wait_for_app() {
    local timeout="${APP_WAIT_TIMEOUT:-300}" waited=0

    until [ -n "${APP_KEY:-}" ] || [ -s "$KEY_FILE" ]; do
        waited=$((waited + 3))
        [ "$waited" -ge "$timeout" ] && fail "APP_KEY tidak tersedia. Isi APP_KEY di Coolify."
        sleep 3
    done
    ensure_app_key

    log "Menunggu migrasi database selesai..."
    until artisan migrate:status >/dev/null 2>&1 && ! artisan migrate:status 2>/dev/null | grep -q "Pending"; do
        waited=$((waited + 5))
        [ "$waited" -ge "$timeout" ] && fail "Migrasi belum selesai setelah ${timeout} detik. Periksa log container app."
        sleep 5
    done

    artisan config:cache >/dev/null
}

# =============================================================================
main() {
    [ -z "${APP_URL:-}" ] && warn "APP_URL kosong — isi dengan alamat situs (mis. https://mtsn2kotamalang.sch.id) agar link di email & sitemap benar."
    prepare_storage
    ensure_app_key

    case "$ROLE" in
        app)
            wait_for_database
            run_migrations
            seed_if_empty
            optimize_app
            log "Siap. Menjalankan: $*"
            exec "$@"
            ;;
        worker|scheduler)
            wait_for_database
            wait_for_app
            log "Siap. Menjalankan: $*"
            exec setpriv --reuid="$APP_USER" --regid="$APP_USER" --init-groups -- env HOME=/tmp "$@"
            ;;
        *)
            # Perintah bebas, mis. `docker compose run --rm app php artisan tinker`
            exec "$@"
            ;;
    esac
}

main "$@"
