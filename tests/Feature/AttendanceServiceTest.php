<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->attendanceService = app(AttendanceService::class);
    $this->office = OfficeLocation::factory()->create([
        'polygon_coordinates' => [
            ['lat' => -6.1750, 'lng' => 106.8260],
            ['lat' => -6.1750, 'lng' => 106.8280],
            ['lat' => -6.1770, 'lng' => 106.8280],
            ['lat' => -6.1770, 'lng' => 106.8260],
        ],
    ]);
    $this->user = User::factory()->pegawai()->create();
});

test('employee records check-in successfully on time within polygon', function () {
    Carbon::setTestNow(Carbon::today()->setTime(7, 30, 0));

    $attendance = $this->attendanceService->recordCheckIn(
        user: $this->user,
        officeLocationId: $this->office->id,
        latitude: -6.1760,
        longitude: 106.8270,
        deviceName: 'iPhone 15 Pro',
        deviceId: 'device-test-123',
        notes: 'Hadir pagi'
    );

    expect($attendance)->toBeInstanceOf(Attendance::class)
        ->and($attendance->status)->toBe(AttendanceStatus::Present)
        ->and($attendance->device_name)->toBe('iPhone 15 Pro')
        ->and($attendance->device_id)->toBe('device-test-123')
        ->and($attendance->user_id)->toBe($this->user->id);
});

test('employee records check-in with late status when past work start time', function () {
    Carbon::setTestNow(Carbon::today()->setTime(8, 15, 0));

    $attendance = $this->attendanceService->recordCheckIn(
        user: $this->user,
        officeLocationId: $this->office->id,
        latitude: -6.1760,
        longitude: 106.8270
    );

    expect($attendance->status)->toBe(AttendanceStatus::Late);
});

test('employee records check-in with outside_area status when outside polygon', function () {
    Carbon::setTestNow(Carbon::today()->setTime(7, 30, 0));

    // Koordinat di luar area polygon kantor
    $outsideLat = -6.2000;
    $outsideLng = 106.8500;

    $attendance = $this->attendanceService->recordCheckIn(
        user: $this->user,
        officeLocationId: $this->office->id,
        latitude: $outsideLat,
        longitude: $outsideLng
    );

    expect($attendance->status)->toBe(AttendanceStatus::OutsideArea)
        ->and($attendance->notes)->toContain('di luar area kantor');
});

test('employee cannot check-in twice on the same day', function () {
    Carbon::setTestNow(Carbon::today()->setTime(7, 30, 0));

    $this->attendanceService->recordCheckIn(
        user: $this->user,
        officeLocationId: $this->office->id,
        latitude: -6.1760,
        longitude: 106.8270
    );

    $this->expectException(ValidationException::class);

    $this->attendanceService->recordCheckIn(
        user: $this->user,
        officeLocationId: $this->office->id,
        latitude: -6.1760,
        longitude: 106.8270
    );
});

test('employee records check-out successfully after check-in', function () {
    Carbon::setTestNow(Carbon::today()->setTime(7, 30, 0));

    $this->attendanceService->recordCheckIn(
        user: $this->user,
        officeLocationId: $this->office->id,
        latitude: -6.1760,
        longitude: 106.8270,
        deviceName: 'iPhone 15 Pro'
    );

    Carbon::setTestNow(Carbon::today()->setTime(17, 5, 0));

    $checkOut = $this->attendanceService->recordCheckOut(
        user: $this->user,
        latitude: -6.1761,
        longitude: 106.8271,
        deviceName: 'iPhone 15 Pro',
        notes: 'Selesai tugas'
    );

    expect($checkOut->check_out_at)->not->toBeNull()
        ->and($checkOut->check_out_device_name)->toBe('iPhone 15 Pro')
        ->and($checkOut->notes)->toContain('Pulang: Selesai tugas');
});

test('employee cannot check-out without prior check-in today', function () {
    Carbon::setTestNow(Carbon::today()->setTime(17, 0, 0));

    $this->expectException(ValidationException::class);

    $this->attendanceService->recordCheckOut(
        user: $this->user,
        latitude: -6.1760,
        longitude: 106.8270
    );
});
