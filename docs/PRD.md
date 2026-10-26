
# Product Requirements Document (PRD)
## SiAbsen — Admin Web

### 1. Ringkasan Produk

**SiAbsen** adalah aplikasi administrasi absensi pegawai yang digunakan oleh **Admin HR** untuk memantau aktivitas kehadiran pegawai, melihat histori/log absensi, serta mengelola dan memetakan lokasi yang diperbolehkan sebagai titik absensi.

Admin Web berfungsi sebagai **monitoring dan management console** bagi Admin HR, bukan sebagai aplikasi absensi pegawai.

### 2. Target Pengguna

**Primary User:** Admin HR

Kebutuhan utama:
- Memantau kondisi absensi pegawai.
- Melihat log kehadiran secara detail.
- Mengetahui lokasi pegawai saat melakukan absensi.
- Memastikan absensi dilakukan pada lokasi yang telah ditentukan.
- Mengelola titik/lokasi absensi.
- Melakukan pencarian dan filtering data absensi.

---

# 3. Struktur Navigasi

```text
SiAbsen Admin
│
├── Login
│
└── Dashboard
    │
    ├── Log Absensi
    │   └── Detail Absensi
    │
    └── Lokasi Absensi
        └── Tambah/Edit Lokasi
```

### Main Navigation

| Menu | Fungsi |
|---|---|
| Dashboard | Ringkasan kondisi absensi |
| Log Absensi | Melihat seluruh transaksi absensi |
| Lokasi Absensi | Melihat dan mengelola titik lokasi absensi |
| Profil Admin | Informasi akun admin |
| Logout | Keluar dari sistem |

---

# 4. Halaman Login

### Tujuan
Memberikan akses aman bagi Admin HR ke sistem SiAbsen.

### Komponen

- Logo SiAbsen
- Identitas/logo Kementerian PPN/Bappenas
- Input Email/Username
- Input Password
- Checkbox "Ingat saya"
- Tombol **Masuk**
- Link "Lupa Password"

### State

- Default
- Loading
- Invalid credential
- Account locked
- Session expired

### Acceptance Criteria

- Admin dapat login menggunakan kredensial yang valid.
- Kredensial yang salah menghasilkan pesan error yang jelas.
- Password tidak ditampilkan secara plain text.
- Setelah login berhasil, admin diarahkan ke Dashboard.

---

# 5. Dashboard

### Tujuan
Memberikan **quick overview** kondisi absensi pegawai hari ini.

### KPI Cards

Minimal menampilkan:

| KPI | Deskripsi |
|---|---|
| Total Pegawai | Jumlah pegawai terdaftar |
| Sudah Absen | Pegawai yang sudah melakukan absensi |
| Belum Absen | Pegawai yang belum melakukan absensi |
| Terlambat | Pegawai yang melakukan absensi setelah batas waktu |
| Lokasi Aktif | Jumlah titik lokasi absensi aktif |

### Visualisasi

Dashboard dapat memiliki:

**Absensi Hari Ini**
- Hadir
- Terlambat
- Belum Absen
- Tidak Hadir

**Tren Absensi**
- Grafik jumlah absensi berdasarkan waktu/hari.

**Aktivitas Terbaru**
- Nama pegawai
- Jenis absensi
- Waktu
- Lokasi
- Status

### Quick Action

- **Lihat Semua Log**
- **Kelola Lokasi**
- **Tambah Lokasi**

---

# 6. Halaman Log Absensi

### Tujuan
Memberikan kemampuan kepada Admin HR untuk mencari, memfilter, dan memonitor seluruh transaksi absensi.

### Filter

Minimal:

- Rentang tanggal
- Nama/NIP pegawai
- Unit kerja
- Jenis absensi
  - Check-in
  - Check-out
- Status
  - Hadir
  - Terlambat
  - Di luar lokasi
  - Ditolak
- Lokasi absensi

### Tabel

| Kolom | Keterangan |
|---|---|
| Waktu | Waktu transaksi |
| Pegawai | Nama pegawai |
| NIP | Identitas pegawai |
| Unit Kerja | Unit pegawai |
| Jenis | Check-in / Check-out |
| Lokasi | Lokasi absensi |
| Jarak | Jarak dari titik lokasi |
| Status | Status transaksi |
| Action | Lihat detail |

### Fitur

- Search
- Filter
- Sorting
- Pagination
- Export data
- View detail

### Status Visual

Gunakan status badge yang mudah dibedakan:

- **Hadir** → positif
- **Terlambat** → warning
- **Di luar lokasi** → error
- **Ditolak** → error
- **Valid** → positif

---

# 7. Detail Absensi Pegawai

### Tujuan

Menampilkan informasi lengkap dari satu transaksi absensi sehingga Admin HR dapat melakukan verifikasi.

### Informasi Pegawai

- Foto
- Nama
- NIP
- Unit kerja
- Jabatan

### Informasi Absensi

- Tanggal
- Waktu
- Jenis absensi
- Status
- Device
- IP Address
- Metode verifikasi

### Informasi Lokasi

Bagian ini menjadi komponen utama halaman.

Tampilkan:

```text
┌─────────────────────────────────────┐
│                                     │
│              MAP                    │
│                                     │
│       ● Lokasi Pegawai              │
│                                     │
│                 ◎ Titik Absensi     │
│                                     │
└─────────────────────────────────────┘
```

Informasi tambahan:

- Koordinat pegawai
- Koordinat titik absensi
- Jarak dari titik absensi
- Radius yang diperbolehkan
- Nama lokasi
- Status validasi lokasi

### Timeline

Jika tersedia, tampilkan riwayat:

```text
08:01  Check-in
       Kantor Pusat
       Valid

17:03  Check-out
       Kantor Pusat
       Valid
```

---

# 8. Halaman Lokasi Absensi

### Tujuan

Memungkinkan Admin HR mengelola titik geografis yang digunakan sebagai validasi lokasi absensi.

### Tampilan

Halaman menggunakan pendekatan **List + Map**.

```text
┌──────────────────────┬───────────────────────────────┐
│ DAFTAR LOKASI        │                               │
│                      │                               │
│ ● Kantor Pusat       │            MAP                │
│   Jakarta            │                               │
│                      │       ●                      │
│ ● Gedung A           │                               │
│   Jakarta            │                ●              │
│                      │                               │
│ [+ Tambah Lokasi]    │                               │
└──────────────────────┴───────────────────────────────┘
```

### Data Lokasi

| Field | Keterangan |
|---|---|
| Nama Lokasi | Nama titik absensi |
| Alamat | Alamat lokasi |
| Latitude | Koordinat latitude |
| Longitude | Koordinat longitude |
| Radius | Radius validasi dalam meter |
| Status | Aktif / Nonaktif |
| Dibuat | Waktu pembuatan |
| Action | Edit / Detail / Nonaktifkan |

### Fitur

- Search lokasi
- Filter status
- View map
- Tambah lokasi
- Edit lokasi
- Aktif/nonaktif lokasi
- Detail lokasi

---

# 9. Form Pemetaan Lokasi Absensi

### Tujuan

Digunakan Admin HR untuk membuat atau mengubah titik lokasi absensi.

### Form

**Informasi Lokasi**

- Nama lokasi *
- Alamat *
- Status
- Keterangan

**Koordinat**

- Latitude *
- Longitude *
- Radius absensi *
- Satuan: meter

**Map Picker**

Admin dapat:

1. Mencari alamat.
2. Menentukan titik pada peta.
3. Drag marker.
4. Menentukan radius.
5. Melihat area coverage secara visual.

Contoh:

```text
             Radius 100 m
          ┌───────────────┐
       ┌──┘               └──┐
      │          ●            │
       └──┐               ┌──┘
          └───────────────┘
                   ↑
             Titik Absensi
```

### Validasi

- Nama lokasi wajib diisi.
- Koordinat harus valid.
- Radius harus lebih besar dari 0.
- Sistem menampilkan preview radius sebelum disimpan.
- Tidak diperbolehkan membuat lokasi dengan konfigurasi yang tidak valid.

### Action

- **Simpan**
- **Batal**

---

# 10. Design System

## Design Direction

Visual SiAbsen mengikuti **brand identity Kementerian PPN/Bappenas**, dengan pendekatan:

**Professional + Government + Modern + Data-oriented**

Karakter visual:

- Clean
- Formal tetapi tidak kaku
- Banyak menggunakan whitespace
- Card-based dashboard
- Data visualization yang sederhana
- Typography yang mudah dibaca
- Map menjadi visual utama pada fitur lokasi

### Warna

Gunakan warna resmi dari logo/brand guideline Kementerian PPN/Bappenas sebagai **brand reference**, kemudian turunkan menjadi:

```text
Primary
├── Brand / Bappenas
├── Brand Dark
└── Brand Light

Semantic
├── Success
├── Warning
├── Error
└── Info

Neutral
├── Background
├── Surface
├── Border
└── Text
```

**Catatan:** nilai HEX final sebaiknya mengikuti brand guideline resmi Bappenas, bukan mengambil warna logo secara perkiraan.

### Typography

Gunakan sans-serif modern dengan hirarki:

```text
Page Title
Section Heading
Card Title
Body
Caption
Label
```

Prioritas utama adalah **readability**, khususnya pada tabel log absensi.

---

# 11. Responsive Design

Admin Web diprioritaskan untuk desktop karena target pengguna adalah Admin HR.

### Desktop

```text
┌────────────┬─────────────────────────────────────┐
│            │ Header                              │
│  Sidebar   ├─────────────────────────────────────┤
│            │                                     │
│ Dashboard  │ Main Content                        │
│ Log        │                                     │
│ Lokasi     │                                     │
│            │                                     │
└────────────┴─────────────────────────────────────┘
```

### Tablet

- Sidebar dapat collapse.
- Tabel dapat horizontal scrolling.
- Map tetap tersedia.

### Mobile

Tidak menjadi prioritas utama, tetapi halaman harus tetap usable.

---

# 12. Non-Functional Requirements

### Security

- Authentication wajib.
- Authorization berdasarkan role.
- Session timeout.
- Password tidak disimpan dalam plaintext.
- Audit log untuk perubahan lokasi.
- Data koordinat pegawai hanya dapat diakses oleh pengguna berwenang.

### Performance

Target awal:

- Dashboard initial load < 3 detik pada koneksi internal normal.
- Tabel mendukung pagination/server-side query.
- Filtering tidak melakukan full-table load ke browser.

### Auditability

Aktivitas kritis dicatat:

- Login
- Logout
- Tambah lokasi
- Edit lokasi
- Aktif/nonaktif lokasi
- Perubahan konfigurasi radius

---

# 13. MVP Scope

### Phase 1 — MVP

**Authentication**
- Login
- Logout

**Dashboard**
- KPI absensi
- Aktivitas terbaru
- Ringkasan status

**Log Absensi**
- List
- Search
- Filter
- Detail

**Lokasi**
- List lokasi
- Map
- Tambah lokasi
- Edit lokasi
- Aktif/nonaktif

### Phase 2

- Export Excel/CSV
- Advanced analytics
- Heatmap absensi
- Rekap per unit kerja
- Rekap bulanan
- Audit log viewer
- Notifikasi anomali absensi

---

# 14. User Journey Utama

### Monitoring Absensi

```text
Login
  ↓
Dashboard
  ↓
Melihat KPI
  ↓
Log Absensi
  ↓
Filter Pegawai / Tanggal / Status
  ↓
Pilih Transaksi
  ↓
Detail Absensi
  ↓
Verifikasi Lokasi
```

### Pengelolaan Lokasi

```text
Dashboard
  ↓
Lokasi Absensi
  ↓
Tambah Lokasi
  ↓
Isi Informasi Lokasi
  ↓
Pilih Titik pada Map
  ↓
Tentukan Radius
  ↓
Preview Area
  ↓
Simpan
```

# 15. Success Metrics

Keberhasilan MVP dapat diukur melalui:

- Admin dapat menemukan transaksi absensi tertentu dengan cepat.
- Admin dapat memverifikasi lokasi absensi dari halaman detail.
- Admin dapat membuat titik lokasi baru tanpa bantuan teknis.
- Admin dapat melihat status absensi harian dari Dashboard.
- Seluruh perubahan konfigurasi lokasi tercatat dalam audit log.

# 16. Prinsip UX Utama

1. **Data absensi harus mudah dicari.**
2. **Status absensi harus dapat dipahami dalam sekali lihat.**
3. **Lokasi harus divisualisasikan, bukan hanya ditampilkan sebagai koordinat.**
4. **Informasi penting ditampilkan sebelum informasi teknis.**
5. **Admin HR tidak perlu memahami aspek teknis GIS untuk mengelola lokasi.**
6. **Setiap tindakan yang mengubah data penting harus memiliki konfirmasi dan audit trail.**