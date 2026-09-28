#!/bin/sh
# Jalankan perintah artisan sebagai www-data dari terminal container.
#
# Terminal Coolify/"docker exec" masuk sebagai root. Menjalankan `php artisan`
# sebagai root bisa membuat file log/cache milik root yang kemudian tidak bisa
# ditulis php-fpm (website error 500). Pakai perintah ini sebagai gantinya:
#
#   app-artisan migrate:status
#   app-artisan tinker
set -e
cd /var/www/html

if [ "$(id -u)" = "0" ]; then
    exec setpriv --reuid=www-data --regid=www-data --init-groups -- \
        env HOME=/tmp XDG_CONFIG_HOME=/tmp php artisan "$@"
fi

exec php artisan "$@"
