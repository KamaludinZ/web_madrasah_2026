# =============================================================================
# Image produksi MTsN 2 Kota Malang (Laravel 12 + Filament 3)
#
# Satu image dipakai untuk 3 peran (lihat docker-compose.yml):
#   - app       : nginx + php-fpm (web & panel admin)          CONTAINER_ROLE=app
#   - worker    : php artisan queue:work (backup/restore/email) CONTAINER_ROLE=worker
#   - scheduler : php artisan schedule:work (tugas terjadwal)   CONTAINER_ROLE=scheduler
#
# Build context: folder mtsn2malang/ (tempat file ini berada).
# =============================================================================

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22
# Rilis Debian untuk image dasar (ganti ke "trixie" bila tag bookworm tidak
# lagi tersedia di Docker Hub).
ARG DEBIAN_SUITE=bookworm

# -----------------------------------------------------------------------------
# Tahap 1 — aset frontend (Vite + Tailwind)
# -----------------------------------------------------------------------------
FROM node:${NODE_VERSION}-${DEBIAN_SUITE}-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# Tailwind memindai view, JS, dan kelas di app/**/*.php
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY app ./app
RUN npm run build

# -----------------------------------------------------------------------------
# Tahap 2 — runtime PHP-FPM + nginx
# -----------------------------------------------------------------------------
FROM php:${PHP_VERSION}-fpm-${DEBIAN_SUITE} AS runtime

# Versi mayor client PostgreSQL WAJIB sama dengan server PostgreSQL
# (pg_dump/psql dipakai fitur Backup & Restore). Default: PostgreSQL 16.
ARG PG_MAJOR=16

# Nilai bawaan produksi. Rahasia (APP_KEY, DB_PASSWORD, dst.) TIDAK ditaruh
# di sini — diisi lewat Environment Variables di Coolify / docker-compose.
ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    CONTAINER_ROLE=app \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_TIMEZONE=Asia/Jakarta \
    APP_LOCALE=id \
    APP_FALLBACK_LOCALE=en \
    APP_FAKER_LOCALE=id_ID \
    APP_MAINTENANCE_DRIVER=file \
    APP_SELF_UPDATE=false \
    TRUSTED_PROXIES=* \
    LOG_CHANNEL=stack \
    LOG_STACK=single,stderr \
    LOG_LEVEL=info \
    DB_CONNECTION=pgsql \
    DB_HOST=db \
    DB_PORT=5432 \
    SESSION_DRIVER=database \
    SESSION_LIFETIME=120 \
    SESSION_SECURE_COOKIE=true \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    FILESYSTEM_DISK=local \
    BROADCAST_CONNECTION=log \
    BCRYPT_ROUNDS=12

# Paket sistem: nginx, supervisor, client PostgreSQL (dari repo resmi PGDG
# agar versinya bisa dipilih), dan pustaka untuk ekstensi PHP.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates curl gnupg nginx procps supervisor unzip; \
    install -d /usr/share/postgresql-common/pgdg; \
    curl -fsSL -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.asc \
        https://www.postgresql.org/media/keys/ACCC4CF8.asc; \
    . /etc/os-release; \
    echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.asc] https://apt.postgresql.org/pub/repos/apt ${VERSION_CODENAME}-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends "postgresql-client-${PG_MAJOR}"; \
    rm -rf /var/lib/apt/lists/*

# Ekstensi PHP yang dipakai aplikasi (gd+webp untuk konversi gambar, intl,
# zip untuk backup/restore, pgsql, pcntl untuk timeout job antrian, dsb).
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath exif gd intl opcache pcntl pdo_pgsql pgsql zip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Konfigurasi server
COPY docker/php/php.ini        /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/php-fpm.conf   /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx/nginx.conf   /etc/nginx/nginx.conf
COPY docker/nginx/site.conf    /etc/nginx/conf.d/default.conf
COPY docker/supervisord.conf   /etc/supervisor/supervisord.conf
COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/app-entrypoint
COPY --chmod=0755 docker/healthcheck.sh /usr/local/bin/app-healthcheck
COPY --chmod=0755 docker/app-artisan.sh /usr/local/bin/app-artisan
RUN rm -f /etc/nginx/sites-enabled/default /usr/local/etc/php-fpm.d/zz-docker.conf

WORKDIR /var/www/html

# Dependensi PHP dulu (lapisan cache terpisah dari kode)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-progress

# Kode aplikasi + hasil build frontend
COPY --chown=www-data:www-data . .
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# Autoload, folder storage, discovery paket & aset Filament (tidak butuh
# database), symlink public/storage.
RUN set -eux; \
    composer dump-autoload --no-dev --optimize --classmap-authoritative --no-scripts; \
    mkdir -p storage/app/public storage/app/private storage/app/backups \
        storage/framework/cache/data storage/framework/sessions storage/framework/views \
        storage/logs bootstrap/cache; \
    APP_KEY=base64:$(head -c 32 /dev/urandom | base64) php artisan package:discover --ansi; \
    APP_KEY=base64:$(head -c 32 /dev/urandom | base64) php artisan filament:assets; \
    ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage; \
    rm -f public/hot bootstrap/cache/config.php bootstrap/cache/routes-v7.php; \
    chown -R www-data:www-data storage bootstrap/cache public

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD ["app-healthcheck"]

ENTRYPOINT ["app-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
