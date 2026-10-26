<?php

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@kantor.com')->first();
    $this->pegawai = User::where('email', 'pegawai@kantor.com')->first();
});

test('unauthenticated user is redirected to admin login', function () {
    $response = $this->get('/admin');

    $response->assertRedirect('/admin/login');
});

test('pegawai user is forbidden from accessing admin panel', function () {
    $response = $this->actingAs($this->pegawai)->get('/admin');

    $response->assertForbidden();
});

test('admin can access dashboard and see SiAbsen branding', function () {
    $response = $this->actingAs($this->admin)->get('/admin');

    $response->assertOk()
        ->assertSee('SiAbsen')
        ->assertSee('Total Pegawai')
        ->assertSee('Sudah Absen')
        ->assertSee('Belum Absen')
        ->assertSee('Terlambat')
        ->assertSee('Lokasi Aktif');
});

test('admin can access log absensi table page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/attendances');

    $response->assertOk()
        ->assertSee('Log Absensi')
        ->assertSee('Waktu Masuk')
        ->assertSee('Nama Pegawai')
        ->assertSee('Unit Kerja');
});

test('admin can access lokasi absensi page and create page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/office-locations');

    $response->assertOk()
        ->assertSee('Lokasi Absensi')
        ->assertSee('Kantor Pusat Jakarta');

    $createResponse = $this->actingAs($this->admin)->get('/admin/office-locations/create');
    $createResponse->assertOk()
        ->assertSee('Nama Lokasi Kantor')
        ->assertSee('Radius Absensi');
});

test('admin can access data pegawai page and create page', function () {
    $response = $this->actingAs($this->admin)->get('/admin/users');

    $response->assertOk()
        ->assertSee('Data Pegawai')
        ->assertSee('Budi Pratama');

    $createResponse = $this->actingAs($this->admin)->get('/admin/users/create');
    $createResponse->assertOk()
        ->assertSee('Informasi Pegawai')
        ->assertSee('Unit Kerja');
});

test('attendance map detail view renders with employee and office info', function () {
    $attendance = Attendance::first();

    $rendered = view('filament.attendances.map-detail', ['record' => $attendance])->render();

    expect($rendered)->toContain($attendance->user->name)
        ->and($rendered)->toContain($attendance->officeLocation->name)
        ->and($rendered)->toContain('attendance-map-'.$attendance->id)
        ->and($rendered)->toContain('Visualisasi Posisi Pegawai vs Area Kantor');
});
