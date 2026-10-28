#!/usr/bin/env bash
set -e

# ==============================================================================
# Script Deploy SiAbsen untuk VPS (Ubuntu/Debian)
# ==============================================================================

echo "=== [1/6] Memeriksa dependensi Docker & Docker Compose ==="
if ! command -v docker &> /dev/null; then
    echo "Docker belum terpasang. Menginstall Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    systemctl enable --now docker
fi

if ! docker compose version &> /dev/null; then
    echo "Docker Compose plugin belum terpasang. Menginstall..."
    apt-get update && apt-get install -y docker-compose-plugin
fi

echo "=== [2/6] Memeriksa file konfigurasi .env & Docker network ==="
if [ ! -f .env ]; then
    echo ".env tidak ditemukan. Menyalin dari .env.example..."
    cp .env.example .env
fi

# Pastikan external network 'proxy' tersedia
if ! docker network inspect proxy >/dev/null 2>&1; then
    echo "Membuat external network 'proxy'..."
    docker network create proxy
fi

echo "=== [3/6] Membangun dan menjalankan container Docker ==="
docker compose pull || true
docker compose up -d --build

echo "=== [4/6] Menginstall PHP & JS dependencies di dalam container ==="
docker compose exec -T app composer install --no-dev --optimize-autoloader --no-interaction
docker compose exec -T app npm ci || docker compose exec -T app npm install
docker compose exec -T app npm run build

echo "=== [5/6] Menjalankan migrasi database & seeding awal (jika kosong) ==="
docker compose exec -T app php artisan migrate --force

# Cek apakah tabel users sudah ada isinya atau masih kosong
USER_COUNT=$(docker compose exec -T app php artisan tinker --execute 'echo \App\Models\User::count();' 2>/dev/null | tr -d '\r\n ' || echo "0")
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo "Database baru/kosong terdeteksi. Menjalankan db:seed..."
    docker compose exec -T app php artisan db:seed --force
else
    echo "Database sudah terisi ($USER_COUNT user), melewati seeding otomatis."
fi

echo "=== [6/6] Optimasi Cache Laravel ==="
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan storage:link || true

echo "=========================================================="
echo " Deployment berhasil! Aplikasi SiAbsen siap digunakan."
echo " Status container:"
docker compose ps
echo "=========================================================="
