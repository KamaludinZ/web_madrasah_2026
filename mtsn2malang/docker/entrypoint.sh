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

is_true() { case "${1:-}" in 1|true|TRUE|True|yes|on) return 0 ;; *) return 1 ;; esac; }

# -----------------------------------------------------------------------------
# 0a. DB_URL (mis. "Postgres URL (internal)" dari Coolify) → DB_HOST, DB_PORT,
#     DB_DATABASE, DB_USERNAME, DB_PASSWORD. Laravel sendiri bisa membaca
#     DB_URL, tetapi pg_isready di sini dan pg_dump/psql (Backup & Restore)
#     memakai nilai terpisah — jadi nilai terpisah selalu diisi.
# -----------------------------------------------------------------------------
apply_db_url() {
    local url="${DB_URL:-${DATABASE_URL:-}}"
    [ -z "$url" ] && return

    local parsed
    parsed="$(DB_URL_RAW="$url" php -r '
        $p = parse_url(getenv("DB_URL_RAW"));
        if ($p === false || empty($p["host"])) { exit(1); }
        $out = [
            "DB_HOST"     => $p["host"],
            "DB_PORT"     => $p["port"] ?? 5432,
            "DB_DATABASE" => ltrim($p["path"] ?? "", "/"),
            "DB_USERNAME" => urldecode($p["user"] ?? ""),
            "DB_PASSWORD" => urldecode($p["pass"] ?? ""),
        ];
        foreach ($out as $k => $v) { if ($v !== "") { echo $k, "=", base64_encode((string) $v), "\n"; } }
    ')" || fail "DB_URL tidak valid. Contoh: postgres://user:password@host:5432/nama_db"

    while IFS='=' read -r key value; do
        [ -n "$key" ] && export "$key=$(printf '%s' "$value" | base64 -d)"
    done <<< "$parsed"
    export DB_CONNECTION=pgsql DB_URL="$url"
    log "Koneksi database diambil dari DB_URL (host: $DB_HOST, database: ${DB_DATABASE:-?})."
}

# -----------------------------------------------------------------------------
# 0b. Nilai turunan yang aman untuk berbagai cara deploy
# -----------------------------------------------------------------------------
apply_runtime_defaults() {
    # Cookie sesi "secure" hanya bila situs memakai HTTPS — domain http://
    # (mis. domain sslip bawaan Coolify) tetap bisa login admin.
    if [ -z "${SESSION_SECURE_COOKIE:-}" ]; then
        case "${APP_URL:-}" in
            https://*) export SESSION_SECURE_COOKIE=true ;;
            *)         export SESSION_SECURE_COOKIE=false ;;
        esac
    fi

    # Worker & scheduler di dalam container app (mode Dockerfile saja).
    local q=false s=false
    if [ "$ROLE" = "app" ]; then
        is_true "${RUN_QUEUE_WORKER:-false}" && q=true
        is_true "${RUN_SCHEDULER:-false}" && s=true
    fi
    export SUPERVISOR_QUEUE_AUTOSTART="$q" SUPERVISOR_SCHEDULER_AUTOSTART="$s"
}

# -----------------------------------------------------------------------------
# 0c. Pilih client PostgreSQL (pg_dump/psql) yang versinya sama dengan server
#     agar Backup & Restore berhasil (dump versi lebih baru bisa gagal
#     direstore ke server lama, dan pg_dump lama menolak server yang lebih baru).
# -----------------------------------------------------------------------------
select_pg_client() {
    [ "${DB_CONNECTION:-pgsql}" != "pgsql" ] && return

    local newest major
    newest="$(ls -d /usr/lib/postgresql/*/bin 2>/dev/null | sort -V | tail -n1)"
    [ -z "$newest" ] && { warn "Client PostgreSQL tidak ditemukan di image."; return; }

    major="$(PGPASSWORD="${DB_PASSWORD:-}" PGCONNECT_TIMEOUT=5 "$newest/psql" \
        -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" -d "${DB_DATABASE:-postgres}" \
        -XAtqc 'SHOW server_version_num' 2>/dev/null | cut -c1-2 || true)"

    if [ -n "$major" ] && [ -x "/usr/lib/postgresql/$major/bin/pg_dump" ]; then
        export PATH="/usr/lib/postgresql/$major/bin:$PATH"
        log "Server PostgreSQL versi $major — memakai pg_dump/psql versi $major."
    elif [ -n "$major" ]; then
        export PATH="$newest:$PATH"
        warn "Server PostgreSQL versi $major tidak punya client yang sama di image; memakai $(basename "$(dirname "$newest")"). Fitur Backup/Restore mungkin gagal."
    else
        export PATH="$newest:$PATH"
        warn "Versi server PostgreSQL tidak terbaca (cek DB_USERNAME/DB_PASSWORD); memakai client terbaru."
    fi
}

# -----------------------------------------------------------------------------
# 0d. Peringatan bila storage bukan volume (data hilang setiap redeploy)
# -----------------------------------------------------------------------------
check_storage_volume() {
    if command -v mountpoint >/dev/null 2>&1 && ! mountpoint -q "$APP_DIR/storage"; then
        warn "Folder $APP_DIR/storage BUKAN volume persisten. Foto/berkas upload, backup,"
        warn "log, dan APP_KEY tersimpan akan HILANG setiap redeploy. Tambahkan Persistent"
        warn "Storage di Coolify dengan Destination Path: $APP_DIR/storage"
    fi
}

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
    apply_db_url
    apply_runtime_defaults
    check_storage_volume
    prepare_storage
    ensure_app_key

    case "$ROLE" in
        app)
            wait_for_database
            select_pg_client
            log "Queue worker di container ini: $SUPERVISOR_QUEUE_AUTOSTART, scheduler: $SUPERVISOR_SCHEDULER_AUTOSTART."
            run_migrations
            seed_if_empty
            optimize_app
            log "Siap. Menjalankan: $*"
            exec "$@"
            ;;
        worker|scheduler)
            wait_for_database
            select_pg_client
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
