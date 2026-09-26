#!/bin/sh
# Health check container sesuai perannya (dipakai HEALTHCHECK Dockerfile).
set -e

case "${CONTAINER_ROLE:-app}" in
    app)
        # /up = route health bawaan Laravel (boot aplikasi tanpa query berat)
        curl -fsS --max-time 4 -o /dev/null http://127.0.0.1:8080/up
        ;;
    worker)
        pgrep -f "artisan queue:work" >/dev/null
        ;;
    scheduler)
        pgrep -f "artisan schedule:work" >/dev/null
        ;;
    *)
        exit 0
        ;;
esac
