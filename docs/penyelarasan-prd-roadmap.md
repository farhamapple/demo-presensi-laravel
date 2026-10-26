# Penyelarasan PRD SiAbsen & Roadmap Pengembangan Aplikasi

Dokumen ini merupakan hasil pencocokan dan integrasi antara **Product Requirements Document (PRD SiAbsen — Admin Web)** dan **Dokumen Tahapan Pengembangan Backend & API**. Dokumen ini menjadi acuan tunggal (*single source of truth*) dalam pengembangan fase berikutnya.

---

## 🔍 1. Hasil Analisis Komparasi & Gap Analysis

| Aspek | Dokumen Tahapan Awal | PRD SiAbsen (Baru) | Keputusan Penyelarasan & Harmonisasi |
|---|---|---|---|
| **Identitas & Branding** | Presensi SDM (Generic) | **SiAbsen — Admin Web** (Identitas Bappenas: Professional, Government, Modern, Data-oriented) | Panel Filament diubah nama & temanya menjadi **SiAbsen**, dengan palet warna navy/blue Bappenas, logo instansi, dan whitespace bersih. |
| **Model Geofencing Lokasi** | Polygon Koordinat JSON (`polygon_coordinates`) | Titik Koordinat (Latitude, Longitude) + Radius meter, visual Map Picker | **Dual-Engine Geofence**: Lokasi memiliki `latitude`, `longitude`, `radius_meters` (PRD) **DAN** `polygon_coordinates` (instruksi awal). Admin HR dapat menentukan radius lingkaran cepat ATAU menggambar polygon detail. |
| **Profil Pegawai** | NIP, Nama, Email, Phone, Status, Password | NIP, Nama, Email, Phone, **Unit Kerja**, **Jabatan**, **Foto Profil** | Menambahkan kolom `unit_kerja`, `jabatan`, dan `avatar_url` pada model & tabel `users`. |
| **Log & Detail Absensi** | Tanggal, Jam Masuk/Pulang, Koordinat, Status, Device Info | Tanggal, Waktu, Jenis (Check-in/out), Unit Kerja, **Jarak dari Titik Kantor**, **IP Address**, Status Badge, **Interactive MAP Detail**, Timeline | Menambahkan `ip_address` dan `distance_meters` pada `attendances`. Halaman detail menyediakan visualisasi peta interaktif: posisi pegawai vs area kantor. |
| **Dashboard Admin** | Ringkasan absensi harian dasar | **5 KPI Cards**: Total Pegawai, Sudah Absen, Belum Absen, Terlambat, Lokasi Aktif + **Tabel Aktivitas Terbaru** | Dashboard Filament disesuaikan dengan 5 KPI Cards eksak sesuai PRD dan widget aktivitas absensi terbaru *real-time*. |
| **Komponen Peta** | Form koordinat text / JSON | **Interactive Map Picker** (Search alamat, drag marker, radius preview, polygon visualization) | Mengintegrasikan Leaflet / OpenStreetMap pada Filament Admin Panel tanpa ketergantungan API berbayar. |

---

## 🏗️ 2. Penyesuaian Skema Database (Menutup Gap PRD)

Untuk mendukung 100% kebutuhan PRD SiAbsen, dilakukan penambahan kolom ringan:

1. **Tabel `users` (Pegawai)**:
   - `unit_kerja` (string, nullable) — Unit kerja/direktorat pegawai
   - `jabatan` (string, nullable) — Jabatan fungsional/struktural
   - `avatar_url` (string, nullable) — URL foto pegawai

2. **Tabel `office_locations` (Lokasi Absensi)**:
   - `latitude` (double, nullable) — Titik pusat koordinat
   - `longitude` (double, nullable) — Titik pusat koordinat
   - `radius_meters` (integer, default 100) — Radius batas toleransi dalam meter
   - `polygon_coordinates` (json, nullable) — Area geofence polygon multi-titik

3. **Tabel `attendances` (Log Transaksi Absensi)**:
   - `distance_meters` (float, nullable) — Jarak pegawai dari titik kantor saat absen
   - `ip_address` (string, nullable) — IP address perangkat saat melakukan request

---

## 📊 3. Status Progres & Roadmap Terpadu SiAbsen

| Fase | Deskripsi Pekerjaan | Status | Catatan |
|---|---|---|---|
| **Fase 1** | Inisialisasi Proyek, Panel Filament & Fondasi Arsitektur | ✅ Selesai | Rebranding ke SiAbsen & Sanctum siap |
| **Fase 2** | Skema Database, Model Eloquent & Seeder | ✅ Selesai | Migrasi penyesuaian gap PRD ditambahkan |
| **Fase 3** | Service Layer & Engine Geofencing (Dual Polygon & Radius) | ✅ Selesai | Ray Casting & Haversine formula teruji |
| **Fase 4** | Panel Admin Web SiAbsen (Filament v5 - PRD Aligned) | ⏳ Siap Dikerjakan | Fokus Dashboard 5 KPI, Log Absensi + Map Modal, Lokasi Map Picker |
| **Fase 5** | API Mobile Pegawai (Sanctum REST API) | ⏳ Menunggu | Endpoint Auth, Sync Lokasi, Check-in/out, History |
| **Fase 6** | Pengujian Terpadu, Dokumentasi API & Finalisasi | ⏳ Menunggu | Pest test menyeluruh & panduan mobile |

---

## 📋 4. Rincian Checklist Terkini per Fase

### ✅ Fase 1: Inisialisasi Proyek & Fondasi Arsitektur
- [x] Inisialisasi Panel Admin Filament (`AdminPanelProvider`) untuk akses Admin SDM.
- [x] Instalasi API routing & Laravel Sanctum (`personal_access_tokens`).
- [x] Konfigurasi Timezone (`Asia/Jakarta` / WIB) dan Lokalisasi Bahasa Indonesia.
- [x] Struktur Service Pattern (`app/Services/`, `app/Enums/`, `app/Http/Requests/Api/`, `app/Http/Resources/Api/`).
- [x] Penyesuaian `User` model dengan otorisasi akses Filament (`FilamentUser` interface & `HasApiTokens` trait).

### ✅ Fase 2: Skema Database, Model Eloquent & Seeder
- [x] Model & Migrasi `User` (NIP, Phone, Role, Status Aktif).
- [x] Model & Migrasi `OfficeLocation` (Nama, Alamat, Polygon Coordinates, Status).
- [x] Model & Migrasi `Attendance` (Check-in/out, GPS, Status Enum, Device Info: `device_name`, `device_id`, `check_out_device_name`).
- [x] Migrasi Tambahan Gap PRD:
  - [x] Menambahkan `unit_kerja`, `jabatan`, `avatar_url` pada `users`.
  - [x] Menambahkan `latitude`, `longitude`, `radius_meters` pada `office_locations`.
  - [x] Menambahkan `distance_meters`, `ip_address` pada `attendances`.
- [x] Enum `UserRole` & `AttendanceStatus`.
- [x] Seeder data awal lengkap (Admin SDM, Pegawai Demo Bappenas dengan unit kerja & jabatan, serta lokasi kantor).

### ✅ Fase 3: Service Layer & Engine Geofencing (Dual Engine)
- [x] `GeofencingService`:
  - [x] Algoritma Ray-Casting untuk validasi polygon.
  - [x] Algoritma Haversine Formula untuk pengukuran jarak eksak (`distance_meters`) dan validasi radius lingkaran.
  - [x] Kalkulasi Centroid polygon.
- [x] `AttendanceService`:
  - [x] Validasi check-in (mencegah double check-in hari yang sama).
  - [x] Validasi check-out (memastikan sudah check-in, mencatat waktu pulang).
  - [x] Penentuan status kehadiran otomatis (`Hadir`, `Terlambat`, `Di Luar Area`).
  - [x] Perekaman info perangkat & IP Address.
- [x] `OfficeLocationService`:
  - [x] Manajemen data kantor, normalisasi polygon, dan pencocokan lokasi terdekat.
- [x] `EmployeeService`:
  - [x] CRUD pegawai, validasi keunikan NIP/email, reset password.
- [x] `AuthService`:
  - [x] Autentikasi mobile pegawai via NIP atau Email, penerbitan token Sanctum.
- [x] Automated Tests Unit & Feature (25 Test Cases passed 100%).

### 🖥️ Fase 4: Panel Admin Web SiAbsen (Filament v5 — PRD Aligned)
Tujuan: Membangun dashboard administrasi web SiAbsen sesuai rancangan UX dan spesifikasi PRD.

- [ ] **Branding & Tema Visual SiAbsen**:
  - [ ] Judul Panel: **SiAbsen — Admin Web**.
  - [ ] Arah desain Bappenas: Modern, professional, clean whitespace, palet warna primer biru/navy instansi.
  - [ ] Navigasi sidebar: Dashboard, Log Absensi, Lokasi Absensi, Data Pegawai.
- [ ] **Dashboard SiAbsen**:
  - [ ] **5 KPI Cards**:
    1. *Total Pegawai* (jumlah pegawai terdaftar).
    2. *Sudah Absen* (pegawai yang sudah presensi hari ini).
    3. *Belum Absen* (pegawai aktif yang belum presensi hari ini).
    4. *Terlambat* (pegawai yang presensi setelah batas waktu 08:00).
    5. *Lokasi Aktif* (jumlah titik lokasi kantor yang aktif).
  - [ ] **Widget Aktivitas Terbaru**: Tabel transaksi absensi masuk/pulang terakhir dengan status visual badge.
- [ ] **Resource Log Absensi (`AttendanceResource`)**:
  - [ ] Kolom tabel: Waktu, Nama Pegawai, NIP, Unit Kerja, Jenis (Masuk / Pulang), Lokasi Kantor, Jarak (meter), Status Badge, Device.
  - [ ] Status visual badge: **Hadir** (hijau/positif), **Terlambat** (kuning/warning), **Di Luar Lokasi** (merah/danger).
  - [ ] Filter canggih: Rentang tanggal, Pegawai, Unit Kerja, Status, Lokasi.
  - [ ] **Modal Detail Absensi Interaktif**:
    - [ ] Identitas pegawai (Foto, Nama, NIP, Unit Kerja, Jabatan).
    - [ ] Informasi transaksi (Waktu, Jenis, Status, Device, IP Address).
    - [ ] **Peta Interaktif (Interactive Map)**: Menampilkan marker posisi pegawai saat absen vs area lokasi kantor, indikator jarak, dan status validasi.
    - [ ] Timeline riwayat Masuk & Pulang hari tersebut.
  - [ ] Fitur Export Log Absensi ke Excel / CSV.
- [ ] **Resource Lokasi Absensi (`OfficeLocationResource`)**:
  - [ ] Tampilan List Lokasi dengan status aktif/nonaktif toggle dan preview titik koordinat.
  - [ ] **Form Pemetaan Lokasi dengan Map Picker**:
    - [ ] Input Nama Lokasi, Alamat, Status.
    - [ ] Input Latitude, Longitude, dan Radius Absensi (meter).
    - [ ] Visualizer Area Geofence (Polygon & Lingkaran Radius) menggunakan Leaflet Map.
- [ ] **Resource Pegawai (`EmployeeResource`)**:
  - [ ] CRUD data pegawai: NIP, Nama, Email, Unit Kerja, Jabatan, Nomor Telepon, Status Keaktifan.

### 📱 Fase 5: API Mobile Pegawai (RESTful API & Sanctum)
Tujuan: Menyediakan API lengkap untuk aplikasi mobile smartphone pegawai.

- [ ] Endpoint Autentikasi:
  - `POST /api/v1/auth/login` (login via NIP/Email).
  - `POST /api/v1/auth/logout`.
  - `GET /api/v1/auth/me` (profil, foto, unit kerja, jabatan).
- [ ] Endpoint Lokasi Kantor:
  - `GET /api/v1/office-locations` (daftar kantor aktif, koordinat pusat, radius, & polygon untuk panduan peta mobile).
- [ ] Endpoint Presensi Pegawai:
  - `POST /api/v1/attendance/check-in` (kirim lat, lng, device info, IP, otomatis hitung jarak & validasi geofence).
  - `POST /api/v1/attendance/check-out` (kirim lat, lng, device info).
  - `GET /api/v1/attendance/today` (status dan timeline absensi hari ini).
  - `GET /api/v1/attendance/history` (riwayat presensi bulanan dengan paginasi).
- [ ] Eloquent API Resources untuk konsistensi respon JSON mobile.

### 🧪 Fase 6: Pengujian Terpadu, Dokumentasi API & Finalisasi
Tujuan: Uji coba menyeluruh, penulisan spesifikasi API, dan perapian kode.

- [ ] Automated Testing (Pest PHP) untuk seluruh alur Filament dan endpoint API.
- [ ] Dokumentasi Lengkap Endpoint API Mobile (`docs/api-spec.md`).
- [ ] Linting & code formatting dengan Laravel Pint (`vendor/bin/pint --format agent`).
- [ ] Panduan integrasi mobile dan deployment.
