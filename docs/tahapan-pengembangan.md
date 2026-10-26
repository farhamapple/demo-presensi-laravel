# Roadmap & Tahapan Pengembangan: Sistem Presensi Pegawai Geofencing Polygon

Dokumen ini berisi rencana kerja bertahap (*phased roadmap*) pengembangan aplikasi backend dan API presensi pegawai berbasis mobile smartphone untuk Admin SDM dan Pegawai.

---

## 📌 Ringkasan Proyek

| Komponen | Spesifikasi |
|---|---|
| **Framework Backend** | Laravel 13 (PHP 8.5) |
| **Admin Panel** | Filament v5 (Livewire 4 + Tailwind CSS) |
| **Pola Arsitektur** | Service Pattern with Separated Concerns (Action/Service Layer) |
| **Database** | SQLite |
| **Autentikasi** | Filament Session Guard (Admin) & Laravel Sanctum API Tokens (Mobile) |
| **Geofencing** | Point-in-Polygon (Ray Casting Algorithm) untuk validasi area kantor polygon |
| **Target Klien** | Web Browser (Admin SDM) & Mobile App iOS/Android (Pegawai) |

---

## 👥 Pengguna & Hak Akses

1. **Admin SDM**:
   - Autentikasi melalui Web Admin Panel Filament.
   - Manajemen Lokasi Kantor & Area Geofence (Polygon kordinat titik sudut kantor).
   - CRUD Data Pegawai (Akun, NIP, Jabatan, Status Keaktifan).
   - Monitoring & Riwayat Rekap Absensi Pegawai (Hadir, Terlambat, Di Luar Radius, dll).

2. **Pegawai**:
   - Autentikasi via API Mobile Smartphone (RESTful API).
   - Melihat daftar dan riwayat absensi pribadi.
   - Melakukan Check-in (Absen Masuk) dan Check-out (Absen Pulang) dengan verifikasi koordinat GPS terhadap polygon kantor.
   - Mengambil data polygon kantor aktif untuk visualisasi peta di aplikasi mobile.

---

## 📊 Status Progres Keseluruhan

| Fase | Deskripsi | Estimasi Status |
|---|---|---|
| **Fase 1** | Inisialisasi Proyek & Fondasi Arsitektur | ✅ Selesai |
| **Fase 2** | Skema Database, Model Eloquent & Seeder | ✅ Selesai |
| **Fase 3** | Implementasi Service Layer & Algoritma Geofencing | ✅ Selesai |
| **Fase 4** | Panel Admin SDM (Filament v5 - SiAbsen PRD) | ✅ Selesai |
| **Fase 5** | API Mobile Pegawai (Laravel Sanctum) | ⏳ Siap Dikerjakan |
| **Fase 6** | Pengujian (Pest Test), Dokumentasi API & Finalisasi | ⏳ Menunggu |

---

## 📋 Rincian Fase & Checklist Pekerjaan

### 🚀 Fase 1: Inisialisasi Proyek & Fondasi Arsitektur
Tujuan: Menyiapkan struktur dasar aplikasi, panel Filament, route API, serta konfigurasi environment.

- [x] Inisialisasi Panel Admin Filament (`AdminPanelProvider`) untuk akses Admin SDM (route `/admin`).
- [x] Instalasi API routing & Laravel Sanctum untuk autentikasi mobile token (`personal_access_tokens` table terpasang).
- [x] Konfigurasi Timezone (`Asia/Jakarta` / WIB) dan Lokalisasi Bahasa Indonesia pada `config/app.php` & `.env`.
- [x] Penyiapan struktur direktori Service Pattern:
  - `app/Services/` (Logika bisnis inti)
  - `app/Enums/UserRole.php` & `app/Enums/AttendanceStatus.php`
  - `app/Http/Requests/Api/` (Validasi input request API)
  - `app/Http/Resources/Api/` (Format transformasi response API)
- [x] Penyesuaian `User` model dengan otorisasi akses Filament (`FilamentUser` interface & `HasApiTokens` trait) agar hanya Admin SDM yang dapat login ke dashboard web.

---

### 🗄️ Fase 2: Skema Database, Model Eloquent & Seeder
Tujuan: Mendesain struktur tabel database SQLite yang optimal, relasi model, serta seed data awal.

- [x] Migration & Model `User` / `Pegawai`:
  - Kolom tambahan: `nip`, `phone`, `role` (`admin`, `pegawai`), `is_active`.
- [x] Migration & Model `OfficeLocation`:
  - Kolom: `name`, `address`, `polygon_coordinates` (JSON array: `[[lat, lng], [lat, lng], ...]`), `is_active`.
- [x] Migration & Model `Attendance`:
  - Kolom: `user_id`, `office_location_id`, `date`, `check_in_at`, `check_out_at`, `check_in_latitude`, `check_in_longitude`, `check_out_latitude`, `check_out_longitude`, `status` (`present`, `late`, `outside_area`), `notes`.
  - Kolom Pelacakan Perangkat (Device Tracking): `device_name` (nama/model smartphone), `device_id` (identifier unik perangkat), `check_out_device_name`.
- [x] Pembuatan Enum:
  - `UserRole`: `Admin`, `Pegawai`.
  - `AttendanceStatus`: `Hadir`, `Terlambat`, `DiLuarRadius`.
- [x] Seeder awal (`DatabaseSeeder`):
  - 1 Akun Admin SDM default (`admin@kantor.com` / `password`).
  - Akun Pegawai demo (`pegawai@kantor.com` / `password`) + 3 pegawai lainnya.
  - Data Lokasi Kantor (Kantor Pusat Jakarta & Cabang Bandung) dengan koordinat polygon riil untuk pengujian geofencing.
  - Data dummy absensi hari ini (tepat waktu, terlambat, dan di luar area).
- [x] Automated Test Model, Relasi, dan Seeder (`tests/Feature/DatabaseAndModelTest.php`).

---

### 🧠 Fase 3: Service Layer & Engine Geofencing Polygon
Tujuan: Memisahkan logika bisnis dari controller & panel, serta membangun algoritma Point-in-Polygon (Ray Casting) murni di PHP agar kompatibel dengan SQLite tanpa extension GIS eksternal.

- [x] `GeofencingService`:
  - Implementasi metode `isPointInPolygon(float $lat, float $lng, array $polygon): bool` menggunakan algoritma *Ray Casting* (Jordan Curve Theorem).
  - Validasi struktur polygon (minimal 3 titik koordinat dan tertutup).
  - Perhitungan titik tengah (*centroid*) polygon sebagai fallback/referensi peta.
  - Perhitungan jarak (Haversine formula) dari titik koordinat ke centroid kantor.
- [x] `AttendanceService`:
  - Logika verifikasi waktu kerja (penentuan status tepat waktu vs terlambat).
  - Validasi absen masuk (mencegah double check-in di hari yang sama).
  - Validasi absen pulang (memeriksa catatan absen masuk hari tersebut, mencatat waktu pulang & device).
  - Integrasi validasi lokasi bersama `GeofencingService`.
  - Perekaman device info (`device_name`, `device_id`, `check_out_device_name`).
- [x] `OfficeLocationService`:
  - Logika bisnis CRUD lokasi kantor & pemformatan GeoJSON/polygon koordinat.
  - Pencarian lokasi kantor terdekat/cocok berdasarkan titik GPS.
- [x] `EmployeeService`:
  - Logika registrasi pegawai, update data profil, reset password, toggle status aktif, dan validasi keunikan NIP/Email.
- [x] `AuthService`:
  - Logika verifikasi kredensial login pegawai (mendukung NIP atau Email) dan penerbitan token Sanctum mobile.
- [x] Unit & Feature Test Service Layer:
  - `tests/Feature/GeofencingServiceTest.php` (6 tests).
  - `tests/Feature/AttendanceServiceTest.php` (6 tests).
  - `tests/Feature/AuthAndEmployeeServiceTest.php` (6 tests).
  - Total 25 tests dalam test suite (**100% Passed**).

---

### 🖥️ Fase 4: Panel Admin SDM (Filament v5 - SiAbsen PRD)
Tujuan: Membangun dashboard administrasi SDM berbasis web yang intuitif, cepat, dan responsif.

- [x] Kustomisasi Tampilan Panel Filament (Branding SiAbsen, Navigasi Admin SDM).
- [x] **Dashboard SiAbsen**:
  - [x] Widget 5 KPI Cards (Total Pegawai, Sudah Absen, Belum Absen, Terlambat, Lokasi Aktif).
  - [x] Widget Aktivitas Absensi Terbaru hari ini secara real-time.
- [x] **Office Location Resource (`OfficeLocationResource`)**:
  - [x] Form input nama kantor, alamat, status aktif.
  - [x] Input titik pusat koordinat (Lat, Lng) dan Radius Absensi (meter).
  - [x] Form Repeater Batas Polygon Geofence.
  - [x] Tabel daftar lokasi kantor dengan status badge dan toggle.
- [x] **Employee Resource (`UserResource`)**:
  - [x] CRUD data pegawai: NIP, nama lengkap, email, unit kerja, jabatan, nomor HP, status aktif, kata sandi.
  - [x] Filter status pegawai dan unit kerja.
- [x] **Attendance Resource (`AttendanceResource`)**:
  - [x] Daftar rekaman log absensi pegawai secara real-time.
  - [x] Filter lanjutan: Rentang tanggal, nama pegawai, status absensi (Hadir, Terlambat, Di Luar Area), unit kerja, dan lokasi kantor.
  - [x] Modal detail absensi interaktif dengan **Leaflet OpenStreetMap**: visualisasi posisi pegawai vs area kantor (circle radius & polygon geofence), info jarak, status validasi, dan identitas pegawai.
- [x] **Automated Tests Panel Admin**:
  - [x] `tests/Feature/FilamentAdminPanelTest.php` (7 test cases passed 100%).

---

### 📱 Fase 5: API Mobile Pegawai (RESTful API & Sanctum)
Tujuan: Menyediakan endpoint API yang aman, standar, dan mudah dikonsumsi oleh aplikasi mobile smartphone.

- [ ] **Endpoint Autentikasi**:
  - `POST /api/v1/auth/login`: Login pegawai menggunakan NIP/Email + Password (mengembalikan token Sanctum & profil).
  - `POST /api/v1/auth/logout`: Revoke/hapus token sesi aktif.
  - `GET /api/v1/auth/me`: Mengambil data profil pegawai yang sedang login.
- [ ] **Endpoint Master Lokasi Kantor**:
  - `GET /api/v1/office-locations`: Mengambil daftar kantor aktif beserta array polygon koordinat untuk ditampilkan pada peta aplikasi mobile.
- [ ] **Endpoint Absensi**:
  - `POST /api/v1/attendance/check-in`: Kirim koordinat GPS (`latitude`, `longitude`), melakukan validasi polygon geofence via `AttendanceService`, lalu menyimpan absensi masuk.
  - `POST /api/v1/attendance/check-out`: Kirim koordinat GPS untuk absen pulang.
  - `GET /api/v1/attendance/today`: Mengambil status absensi pegawai hari ini (apakah sudah absen masuk, jam berapa, status).
  - `GET /api/v1/attendance/history`: Riwayat daftar absensi pegawai (mendukung pagination dan filter bulan/tahun).
- [ ] Standardisasi format respons JSON menggunakan Eloquent API Resource:
  - `UserResource`, `AttendanceResource`, `OfficeLocationResource`.
- [ ] Validasi Request ketat (`StoreCheckInRequest`, `StoreCheckOutRequest`, `LoginRequest`).

---

### 🧪 Fase 6: Pengujian, Dokumentasi API & Finalisasi
Tujuan: Memastikan kestabilan sistem, integritas data, kerapian kode, dan kesiapan integrasi mobile.

- [ ] Automated Testing (Pest PHP):
  - Feature test autentikasi API mobile (login berhasil, login gagal, unauthorized).
  - Feature test alur absensi check-in/check-out (di dalam polygon vs di luar polygon).
  - Feature test akses panel admin (proteksi role pegawai vs admin).
- [ ] Penyusunan Panduan Integrasi API Mobile (`docs/api-spec.md`):
  - Daftar endpoint, header autentikasi, contoh payload request JSON, dan format response sukses & error.
- [ ] Code formatting & linting dengan Laravel Pint:
  - Eksekusi `vendor/bin/pint --format agent` untuk menjaga keseragaman style kode.
- [ ] Verifikasi manual alur keseluruhan sistem.

---

## 📐 Catatan Implementasi Algoritma Geofencing Polygon

Karena database yang digunakan adalah SQLite (tanpa extension SpatiaLite/PostGIS), validasi koordinat titik ($P$) terhadap area polygon kantor ($V_0, V_1, ..., V_n$) dilakukan di level aplikasi menggunakan **Ray Casting Algorithm**:

$$\text{Titik berada di dalam polygon jika garis horizontal dari titik tersebut memotong sisi polygon sebanyak ganjil (odd).}$$

Format penyimpanan koordinat polygon di database menggunakan kolom JSON:
```json
[
  {"lat": -6.208763, "lng": 106.845599},
  {"lat": -6.208900, "lng": 106.846200},
  {"lat": -6.209500, "lng": 106.846000},
  {"lat": -6.209300, "lng": 106.845400}
]
```
Hal ini memastikan aplikasi tetap mandiri, sangat cepat, mudah diuji (*testable*), dan tidak bergantung pada konfigurasi driver server khusus.

