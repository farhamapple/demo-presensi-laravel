# 🏢 SiAbsen — Sistem Monitoring & API Presensi Pegawai Geofencing

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/Filament-v5.x-FFA500?style=for-the-badge&logo=filament&logoColor=white" alt="Filament v5" />
  <img src="https://img.shields.io/badge/PHP-8.5-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.5" />
  <img src="https://img.shields.io/badge/Database-SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite" />
  <img src="https://img.shields.io/badge/Tests-45%20Passed-brightgreen?style=for-the-badge&logo=pest&logoColor=white" alt="Pest Tests" />
</p>

> [!NOTE]
> ⚠️ **DISCLAIMER: HANYA UNTUK KEPERLUAN DEMO & PEMBELAJARAN**
> 
> Proyek **SiAbsen** ini dikembangkan secara khusus untuk **keperluan demonstrasi teknis dan pembelajaran** (*educational & learning purposes only*). Repositori ini mendemonstrasikan implementasi praktik terbaik (*best practices*) arsitektur modern pada ekosistem Laravel 13, Filament v5, Service Pattern terisolasi, algoritma Geofencing Poligon murni (*Jordan Curve Theorem Ray-Casting*), dan Mobile RESTful API dengan Laravel Sanctum.
> 
> Seluruh data seperti akun pengguna, nomor induk pegawai (NIP), nomor telepon, titik koordinat kantor, serta log presensi di dalamnya merupakan **data percontohan (*dummy*)**. Jangan mempergunakan kredensial atau konfigurasi bawaan proyek ini pada lingkungan *production* publik tanpa pengamanan tambahan yang memadai.

**SiAbsen** adalah platform administrasi dan *Application Programming Interface* (API) presensi pegawai berbasis mobile smartphone. Aplikasi ini dirancang untuk **Admin HR / SDM** guna memantau aktivitas kehadiran pegawai secara *real-time*, memetakan titik lokasi absensi kantor (*dual geofencing: polygon & radius*), serta menyediakan *endpoint* RESTful API bagi aplikasi mobile pegawai.

---

## 👥 Pengguna & Hak Akses

1. **Admin HR / SDM (Web Console - Filament v5)**
   - Akses via browser di `/admin`.
   - Memantau kondisi absensi harian melalui **5 KPI Cards** & riwayat aktivitas terbaru.
   - Mengelola titik lokasi kantor dengan validasi titik pusat koordinat, radius toleransi, dan polygon geofence.
   - Mengelola data pegawai (NIP, Unit Kerja, Jabatan, Nomor Telepon, Status Keaktifan).
   - Melihat log presensi pegawai lengkap dengan **Peta Interaktif (Leaflet OSM)** yang memvisualisasikan posisi pegawai saat absen vs area kantor.

2. **Pegawai (Mobile Smartphone Client)**
   - Akses via RESTful API yang aman menggunakan **Laravel Sanctum Bearer Token**.
   - Login menggunakan Email atau NIP + Password.
   - Mengambil data polygon lokasi kantor aktif untuk tampilan peta di smartphone.
   - Melakukan **Check-In (Absen Masuk)** dan **Check-Out (Absen Pulang)** dengan validasi koordinat GPS instan.
   - Perekaman identitas perangkat smartphone (*Device Tracking* & *Anti-Fraud*) dan IP address.
   - Memantau status presensi hari ini serta riwayat kehadiran bulanan.

---

## 📐 Arsitektur & Mesin Geofencing

Sistem menggunakan arsitektur **Service Pattern with Separated Concerns**:
- **`GeofencingService`**: Mengimplementasikan **Algoritma Ray-Casting (Jordan Curve Theorem)** murni di PHP untuk memvalidasi apakah koordinat GPS berada di dalam batas polygon kantor tanpa ketergantungan ekstensi PostGIS/SpatiaLite. Dilengkapi **Formula Haversine** untuk mengukur jarak eksak (dalam satuan meter) jika pegawai berada di luar area kantor.
- **`AttendanceService`**: Mengelola seluruh *state* transaksi presensi, pencegahan *double check-in*, penentuan status (`Hadir`, `Terlambat`, `Di Luar Area`), dan kalkulasi jarak.
- **`OfficeLocationService`**: Manajemen lokasi kantor dan pencocokan koordinat GPS terdekat.
- **`EmployeeService`**: Manajemen siklus data kepegawaian.
- **`AuthService`**: Autentikasi NIP/Email dan manajemen token Sanctum.

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi

### 1. Prasyarat Sistem
- PHP >= 8.3 (Disarankan PHP 8.4+)
- Composer
- Node.js & NPM
- SQLite Extension

### 2. Langkah Instalasi

Clone repositori dan masuk ke direktori proyek:
```bash
git clone https://github.com/farhamapple/demo-presensi-laravel.git
cd demo-presensi-laravel
```

Pasang dependensi PHP:
```bash
composer install
```

Salin file environment dan generate application key:
```bash
cp .env.example .env
php artisan key:generate
```

Jalankan migrasi database, pengisian data demo (*seeding*), dan publish aset Filament:
```bash
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan filament:upgrade
```

Jalankan server aplikasi:
```bash
php artisan serve
```
Aplikasi backend dan API akan aktif di `http://localhost:8000`.

---

## 🔐 Kredensial Akun Pengujian (Demo)

### 1. Admin SDM (Web Panel)
- **URL**: [http://localhost:8000/admin](http://localhost:8000/admin)
- **Email**: `admin@kantor.com`
- **Password**: `password`
- **Peran**: Admin HR (Akses penuh ke Dashboard, Log Absensi, Lokasi, dan Data Pegawai)

### 2. Pegawai Demo (Mobile Client / API)
- **Email**: `pegawai@kantor.com`
- **NIP**: `199205122018021002`
- **Password**: `password`
- **Peran**: Pegawai (Akses API Mobile)

---

## 📱 Ringkasan Endpoint API Mobile (`/api/v1`)

| Metode | Endpoint | Deskripsi | Autentikasi |
|---|---|---|---|
| `POST` | `/api/v1/auth/login` | Login pegawai via NIP/Email + Password | Public |
| `POST` | `/api/v1/auth/logout` | Revoke/hapus token sesi aktif | Bearer Token |
| `GET` | `/api/v1/auth/me` | Ambil profil lengkap pegawai yang login | Bearer Token |
| `GET` | `/api/v1/office-locations` | Daftar lokasi kantor aktif, radius, & polygon | Bearer Token |
| `POST` | `/api/v1/attendance/check-in` | Absen masuk dengan validasi geofence GPS | Bearer Token |
| `POST` | `/api/v1/attendance/check-out` | Absen pulang | Bearer Token |
| `GET` | `/api/v1/attendance/today` | Status dan timeline presensi hari ini | Bearer Token |
| `GET` | `/api/v1/attendance/history` | Riwayat daftar presensi (pagination & filter) | Bearer Token |

> 📖 **Dokumentasi Lengkap API**: Silakan baca dokumen [docs/api-spec.md](docs/api-spec.md) untuk melihat contoh payload *Request* dan *Response* JSON setiap endpoint.

---

## 🧪 Pengujian Otomatis (Testing)

Proyek ini menggunakan **Pest PHP** dengan cakupan pengujian komprehensif pada lapisan Database, Service Layer (Geofencing Ray Casting & Attendance), Panel Admin Filament, dan RESTful API Mobile:

```bash
php artisan test
```

Hasil pengujian:
```text
  PASS  Tests\Feature\AttendanceServiceTest
  ✓ employee records check-in successfully on time within polygon
  ✓ employee records check-in with late status when past work start time
  ✓ employee records check-in with outside_area status when outside polygon
  ✓ employee cannot check-in twice on the same day
  ✓ employee records check-out successfully after check-in
  ✓ employee cannot check-out without prior check-in today

  PASS  Tests\Feature\AuthAndEmployeeServiceTest
  ✓ employee can login via email and receive token
  ✓ employee can login via NIP and receive token
  ✓ inactive employee cannot login
  ✓ admin cannot login via employee mobile auth
  ✓ employee service creates employee with hashed password and pegawai role
  ✓ employee service rejects duplicate email or NIP

  PASS  Tests\Feature\DatabaseAndModelTest
  ✓ admin user can access filament panel
  ✓ pegawai user cannot access filament panel
  ✓ office location casts polygon coordinates to array
  ✓ attendance relationships, status enum casting, and device info work correctly
  ✓ database seeder populates default data correctly

  PASS  Tests\Feature\FilamentAdminPanelTest
  ✓ unauthenticated user is redirected to admin login
  ✓ pegawai user is forbidden from accessing admin panel
  ✓ admin can access dashboard and see SiAbsen branding
  ✓ admin can access log absensi table page
  ✓ admin can access lokasi absensi page and create page
  ✓ admin can access data pegawai page and create page
  ✓ attendance map detail view renders with employee and office info

  PASS  Tests\Feature\GeofencingServiceTest
  ✓ detects point correctly inside polygon
  ✓ detects point correctly outside polygon
  ✓ returns false for polygon with less than 3 vertices
  ✓ validates valid polygon structure
  ✓ calculates centroid correctly
  ✓ calculates distance in meters between two coordinates

  PASS  Tests\Feature\MobileApiTest
  ✓ employee can login via email and receive token
  ✓ employee can login via NIP
  ✓ login fails with wrong password
  ✓ authenticated employee can fetch profile via /me
  ✓ authenticated employee can logout and revoke token
  ✓ employee can get active office locations with coordinates and polygon
  ✓ employee records check-in successfully within geofence
  ✓ employee records check-in outside geofence marked as outside_area
  ✓ employee cannot check-in twice on same day via api
  ✓ employee can check-out after check-in
  ✓ employee cannot check-out without check-in first
  ✓ employee can get today attendance status
  ✓ employee can get paginated attendance history

  Tests:    45 passed (175 assertions)
  Duration: 1.74s
```

---

## 🐳 Deployment & CI/CD

### 1. Menjalankan dengan Docker (FrankenPHP)
Aplikasi telah dikonfigurasi dengan FrankenPHP + Supervisor (FrankenPHP + Queue Worker + Vite) serta integrasi network `proxy` untuk reverse proxy.

```bash
# Jalankan container
docker compose up -d --build
```
Aplikasi lokal akan berjalan pada `http://localhost:8001` (Vite dev server pada port `5175`).

### 2. Deployment Otomatis di VPS dengan GitHub Actions
Repositori ini dilengkapi pipeline CI/CD otomatis di `.github/workflows/ci-cd.yml`:
1. **Testing & Code Style**: Menjalankan Pest test suite dan Laravel Pint setiap push/PR.
2. **Auto Deploy**: Ketika push ke branch `main`, workflow akan login ke VPS via SSH dan menjalankan skrip `deploy.sh`.

#### Konfigurasi GitHub Repository Secret:
Buka repositori GitHub Anda di **Settings > Secrets and variables > Actions > New repository secret**.

Anda **hanya membutuhkan 1 secret**:

| Secret Name | Keterangan |
| :--- | :--- |
| `SSH_KEY` | Isi seluruh private key SSH Anda (`~/.ssh/id_rsa` atau `~/.ssh/id_ed25519`) yang diizinkan login ke user `ubuntu` di VPS. |

> [!NOTE]
> Parameter lainnya sudah memiliki nilai *default*:
> - **Host**: `demo-presensi.farham.my.id` (bisa di-override dengan secret `VPS_HOST`)
> - **Username**: `ubuntu` (bisa di-override dengan secret `VPS_USERNAME`)
> - **Port**: `22` (bisa di-override dengan secret `VPS_PORT`)
> - **Deploy Path**: `/home/ubuntu/apps/demo-presensi-laravel` (bisa di-override dengan secret `VPS_DEPLOY_PATH`)

### 3. Deploy Manual via Script di VPS
Jika ingin deploy manual tanpa GitHub Actions:
```bash
cd /home/ubuntu/apps/demo-presensi-laravel
chmod +x deploy.sh
./deploy.sh
```

---

## 📂 Struktur Dokumentasi Proyek

- [docs/api-spec.md](docs/api-spec.md): Spesifikasi lengkap integrasi endpoint API Mobile.
- [docs/penyelarasan-prd-roadmap.md](docs/penyelarasan-prd-roadmap.md): Penyelarasan PRD SiAbsen dan roadmap terpadu.
- [docs/PRD.md](docs/PRD.md): Dokumen Product Requirements Document awal.
- [docs/tahapan-pengembangan.md](docs/tahapan-pengembangan.md): Lembar pelacak checklist pengerjaan fase 1 s/d 6.

---

## 📄 Lisensi

Aplikasi ini dilisensikan di bawah [MIT License](LICENSE).
