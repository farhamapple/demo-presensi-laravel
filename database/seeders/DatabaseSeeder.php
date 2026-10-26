<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Akun Admin SDM
        $admin = User::firstOrCreate(
            ['email' => 'admin@kantor.com'],
            [
                'name' => 'Admin SDM',
                'nip' => '198501012010011001',
                'unit_kerja' => 'Biro Sumber Daya Manusia',
                'jabatan' => 'Pranata SDM Aparatur Ahli Madya',
                'phone' => '081234567890',
                'role' => UserRole::Admin,
                'is_active' => true,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Buat Akun Pegawai Demo
        $pegawaiUtama = User::firstOrCreate(
            ['email' => 'pegawai@kantor.com'],
            [
                'name' => 'Budi Pratama',
                'nip' => '199205122018021002',
                'unit_kerja' => 'Direktorat Aparatur Negara',
                'jabatan' => 'Pranata Komputer Ahli Muda',
                'phone' => '081298765432',
                'role' => UserRole::Pegawai,
                'is_active' => true,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Pegawai tambahan
        $pegawaiList = [
            $pegawaiUtama,
            User::firstOrCreate(
                ['email' => 'siti.aminah@kantor.com'],
                [
                    'name' => 'Siti Aminah',
                    'nip' => '199408202020012001',
                    'unit_kerja' => 'Pusat Data dan Informasi',
                    'jabatan' => 'Analis Data dan Informasi',
                    'phone' => '081311223344',
                    'role' => UserRole::Pegawai,
                    'is_active' => true,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            ),
            User::firstOrCreate(
                ['email' => 'ahmad.fauzi@kantor.com'],
                [
                    'name' => 'Ahmad Fauzi',
                    'nip' => '199103152017011003',
                    'unit_kerja' => 'Biro Perencanaan dan Organisasi',
                    'jabatan' => 'Perencana Ahli Pertama',
                    'phone' => '081355667788',
                    'role' => UserRole::Pegawai,
                    'is_active' => true,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            ),
            User::firstOrCreate(
                ['email' => 'dewi.lestari@kantor.com'],
                [
                    'name' => 'Dewi Lestari',
                    'nip' => '199611052021022002',
                    'unit_kerja' => 'Direktorat Ketenagakerjaan',
                    'jabatan' => 'Analis Kebijakan Ahli Pertama',
                    'phone' => '081399001122',
                    'role' => UserRole::Pegawai,
                    'is_active' => true,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            ),
        ];

        // 3. Buat Lokasi Kantor dengan Titik Pusat Koordinat, Radius, & Polygon Geofence
        // Kantor Pusat Jakarta (Bappenas / Medan Merdeka Barat)
        $kantorPusat = OfficeLocation::firstOrCreate(
            ['name' => 'Kantor Pusat Jakarta'],
            [
                'address' => 'Jl. Taman Suropati No. 2, Menteng, Jakarta Pusat',
                'latitude' => -6.175650,
                'longitude' => 106.827250,
                'radius_meters' => 150,
                'polygon_coordinates' => [
                    ['lat' => -6.174800, 'lng' => 106.826000],
                    ['lat' => -6.174800, 'lng' => 106.828500],
                    ['lat' => -6.176500, 'lng' => 106.828500],
                    ['lat' => -6.176500, 'lng' => 106.826000],
                ],
                'is_active' => true,
            ]
        );

        // Kantor Cabang Bandung (Gedung Perencanaan Jawa Barat)
        $kantorBandung = OfficeLocation::firstOrCreate(
            ['name' => 'Kantor Perwakilan Bandung'],
            [
                'address' => 'Jl. Diponegoro No. 22, Citarum, Bandung',
                'latitude' => -6.902500,
                'longitude' => 107.619250,
                'radius_meters' => 120,
                'polygon_coordinates' => [
                    ['lat' => -6.901500, 'lng' => 107.618000],
                    ['lat' => -6.901500, 'lng' => 107.620500],
                    ['lat' => -6.903500, 'lng' => 107.620500],
                    ['lat' => -6.903500, 'lng' => 107.618000],
                ],
                'is_active' => true,
            ]
        );

        // 4. Data Dummy Absensi Hari Ini
        $today = Carbon::today();

        // Pegawai 1: Hadir Tepat Waktu (di dalam kantor)
        Attendance::updateOrCreate(
            ['user_id' => $pegawaiList[0]->id, 'date' => $today->toDateString()],
            [
                'office_location_id' => $kantorPusat->id,
                'check_in_at' => $today->copy()->setTime(7, 45, 0),
                'check_out_at' => $today->copy()->setTime(17, 10, 0),
                'check_in_latitude' => -6.175500,
                'check_in_longitude' => 106.827000,
                'check_out_latitude' => -6.175520,
                'check_out_longitude' => 106.827050,
                'distance_meters' => 24.5,
                'status' => AttendanceStatus::Present,
                'notes' => 'Presensi tepat waktu via mobile',
                'device_name' => 'Samsung Galaxy S23 (Android 14)',
                'device_id' => 'sec-android-d92a101f',
                'ip_address' => '192.168.10.45',
                'check_out_device_name' => 'Samsung Galaxy S23 (Android 14)',
            ]
        );

        // Pegawai 2: Terlambat
        Attendance::updateOrCreate(
            ['user_id' => $pegawaiList[1]->id, 'date' => $today->toDateString()],
            [
                'office_location_id' => $kantorPusat->id,
                'check_in_at' => $today->copy()->setTime(8, 25, 0),
                'check_out_at' => null,
                'check_in_latitude' => -6.175600,
                'check_in_longitude' => 106.827100,
                'check_out_latitude' => null,
                'check_out_longitude' => null,
                'distance_meters' => 18.2,
                'status' => AttendanceStatus::Late,
                'notes' => 'Terlambat karena kendala lalu lintas',
                'device_name' => 'iPhone 15 Pro (iOS 17.5)',
                'device_id' => 'apple-ios-98ab45cd',
                'ip_address' => '192.168.10.88',
                'check_out_device_name' => null,
            ]
        );

        // Pegawai 3: Di luar area (koordinat di luar polygon & radius)
        Attendance::updateOrCreate(
            ['user_id' => $pegawaiList[2]->id, 'date' => $today->toDateString()],
            [
                'office_location_id' => $kantorPusat->id,
                'check_in_at' => $today->copy()->setTime(8, 05, 0),
                'check_out_at' => null,
                'check_in_latitude' => -6.200000,
                'check_in_longitude' => 106.850000,
                'check_out_latitude' => null,
                'check_out_longitude' => null,
                'distance_meters' => 3820.0,
                'status' => AttendanceStatus::OutsideArea,
                'notes' => 'Mencoba absen di luar area kantor',
                'device_name' => 'Xiaomi 13T (MIUI 14)',
                'device_id' => 'xiaomi-android-77ba1122',
                'ip_address' => '10.20.30.15',
                'check_out_device_name' => null,
            ]
        );
    }
}
