<?php

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin user can access filament panel', function () {
    $admin = User::factory()->admin()->create();

    $panel = Filament\Facades\Filament::getCurrentOrDefaultPanel();

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isPegawai())->toBeFalse()
        ->and($admin->canAccessPanel($panel))->toBeTrue();
});

test('pegawai user cannot access filament panel', function () {
    $pegawai = User::factory()->pegawai()->create();

    $panel = Filament\Facades\Filament::getCurrentOrDefaultPanel();

    expect($pegawai->isPegawai())->toBeTrue()
        ->and($pegawai->isAdmin())->toBeFalse()
        ->and($pegawai->canAccessPanel($panel))->toBeFalse();
});

test('office location casts polygon coordinates to array', function () {
    $location = OfficeLocation::factory()->create([
        'polygon_coordinates' => [
            ['lat' => -6.175, 'lng' => 106.826],
            ['lat' => -6.175, 'lng' => 106.828],
            ['lat' => -6.176, 'lng' => 106.828],
            ['lat' => -6.176, 'lng' => 106.826],
        ],
    ]);

    expect($location->polygon_coordinates)->toBeArray()
        ->and($location->polygon_coordinates)->toHaveCount(4)
        ->and($location->is_active)->toBeTrue();
});

test('attendance relationships, status enum casting, and device info work correctly', function () {
    $attendance = Attendance::factory()->create([
        'status' => AttendanceStatus::Present,
        'device_name' => 'iPhone 15 Pro',
        'device_id' => 'device-uuid-12345',
    ]);

    expect($attendance->user)->toBeInstanceOf(User::class)
        ->and($attendance->officeLocation)->toBeInstanceOf(OfficeLocation::class)
        ->and($attendance->status)->toBe(AttendanceStatus::Present)
        ->and($attendance->status->label())->toBe('Hadir')
        ->and($attendance->device_name)->toBe('iPhone 15 Pro')
        ->and($attendance->device_id)->toBe('device-uuid-12345');
});

test('database seeder populates default data correctly', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('users', [
        'email' => 'admin@kantor.com',
        'role' => UserRole::Admin->value,
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'pegawai@kantor.com',
        'role' => UserRole::Pegawai->value,
    ]);

    $this->assertDatabaseHas('office_locations', [
        'name' => 'Kantor Pusat Jakarta',
    ]);

    expect(Attendance::count())->toBeGreaterThanOrEqual(3);
});
