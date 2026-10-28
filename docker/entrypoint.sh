#!/bin/sh
set -e

# Pastikan folder storage, cache, dan database selalu tersedia dan writable
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache \
         /app/database \
         /var/log/supervisor

# Pastikan file sqlite database ada jika menggunakan SQLite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    touch /app/database/database.sqlite
    chmod 666 /app/database/database.sqlite 2>/dev/null || true
fi

# Berikan hak akses writable ke direktori tanpa mengubah permission file .gitignore
find /app/storage /app/bootstrap/cache -type d -exec chmod 777 {} + 2>/dev/null || true
find /app/storage /app/bootstrap/cache -type f ! -name ".gitignore" -exec chmod 666 {} + 2>/dev/null || true
find /app/storage /app/bootstrap/cache -name ".gitignore" -exec chmod 644 {} + 2>/dev/null || true

# Otomatis generate APP_KEY jika belum ada di .env dan autoload tersedia
if [ -f /app/.env ] && [ -f /app/vendor/autoload.php ]; then
    if ! grep -q "^APP_KEY=base64:" /app/.env; then
        echo "[Entrypoint] Generating APP_KEY..."
        php /app/artisan key:generate --force
    fi
fi

# Otomatis jalankan migrasi database saat container start jika diaktifkan (default: true)
if [ "${AUTO_MIGRATE:-true}" = "true" ] && [ -f /app/vendor/autoload.php ]; then
    echo "[Entrypoint] Running database migrations..."
    php /app/artisan migrate --force || true
fi

# Otomatis jalankan database seeder jika diaktifkan (default: false, bisa diset true via ENV saat deploy pertama)
if [ "${AUTO_SEED:-false}" = "true" ] && [ -f /app/vendor/autoload.php ]; then
    echo "[Entrypoint] Running database seeder..."
    php /app/artisan db:seed --force || true
fi

# Jalankan perintah yang dilewatkan ke container
exec "$@"

