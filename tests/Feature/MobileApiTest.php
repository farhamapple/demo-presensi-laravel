<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->office = OfficeLocation::factory()->create([
        'name' => 'Kantor Pusat Jakarta',
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
    ]);

    $this->employee = User::factory()->pegawai()->create([
        'name' => 'Budi Pratama',
        'email' => 'budi@kantor.com',
        'nip' => '199205122018021002',
        'unit_kerja' => 'Direktorat Aparatur Negara',
        'jabatan' => 'Pranata Komputer Ahli Muda',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
});

test('employee can login via email and receive token', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => 'budi@kantor.com',
        'password' => 'password123',
        'device_name' => 'Samsung Galaxy S23',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'nip', 'unit_kerja', 'jabatan', 'role', 'is_active'],
                'token',
                'token_type',
            ],
        ])
        ->assertJsonPath('data.user.email', 'budi@kantor.com')
        ->assertJsonPath('data.token_type', 'Bearer');
});

test('employee can login via NIP', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => '199205122018021002',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.user.nip', '199205122018021002');
});

test('login fails with wrong password', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'identifier' => 'budi@kantor.com',
        'password' => 'wrong-pass',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['identifier']);
});

test('authenticated employee can fetch profile via /me', function () {
    Sanctum::actingAs($this->employee);

    $response = $this->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.id', $this->employee->id)
        ->assertJsonPath('data.name', 'Budi Pratama')
        ->assertJsonPath('data.unit_kerja', 'Direktorat Aparatur Negara');
});

test('authenticated employee can logout and revoke token', function () {
    $token = $this->employee->createToken('test-token');

    $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJson(['message' => 'Logout berhasil. Sesi token telah dicabut.']);

    expect($this->employee->tokens()->count())->toBe(0);
});

test('employee can get active office locations with coordinates and polygon', function () {
    Sanctum::actingAs($this->employee);

    $response = $this->getJson('/api/v1/office-locations');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Kantor Pusat Jakarta')
        ->assertJsonPath('data.0.radius_meters', 150)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'address', 'latitude', 'longitude', 'radius_meters', 'polygon_coordinates', 'is_active'],
            ],
        ]);
});

test('employee records check-in successfully within geofence', function () {
    Sanctum::actingAs($this->employee);
    Carbon::setTestNow(Carbon::today()->setTime(7, 45, 0));

    $response = $this->postJson('/api/v1/attendance/check-in', [
        'office_location_id' => $this->office->id,
        'latitude' => -6.175500,
        'longitude' => 106.827000,
        'device_name' => 'iPhone 15 Pro',
        'device_id' => 'ios-device-uuid-999',
        'notes' => 'Presensi pagi',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status.value', AttendanceStatus::Present->value)
        ->assertJsonPath('data.device_name', 'iPhone 15 Pro')
        ->assertJsonPath('data.device_id', 'ios-device-uuid-999');

    $this->assertDatabaseHas('attendances', [
        'user_id' => $this->employee->id,
        'office_location_id' => $this->office->id,
        'device_name' => 'iPhone 15 Pro',
    ]);
});

test('employee records check-in outside geofence marked as outside_area', function () {
    Sanctum::actingAs($this->employee);
    Carbon::setTestNow(Carbon::today()->setTime(7, 45, 0));

    // Koordinat jauh di luar kantor (>3 km)
    $response = $this->postJson('/api/v1/attendance/check-in', [
        'office_location_id' => $this->office->id,
        'latitude' => -6.210000,
        'longitude' => 106.850000,
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status.value', AttendanceStatus::OutsideArea->value);
});

test('employee cannot check-in twice on same day via api', function () {
    Sanctum::actingAs($this->employee);
    Carbon::setTestNow(Carbon::today()->setTime(7, 45, 0));

    $this->postJson('/api/v1/attendance/check-in', [
        'office_location_id' => $this->office->id,
        'latitude' => -6.175500,
        'longitude' => 106.827000,
    ])->assertStatus(201);

    $secondAttempt = $this->postJson('/api/v1/attendance/check-in', [
        'office_location_id' => $this->office->id,
        'latitude' => -6.175500,
        'longitude' => 106.827000,
    ]);

    $secondAttempt->assertStatus(422)
        ->assertJsonValidationErrors(['attendance']);
});

test('employee can check-out after check-in', function () {
    Sanctum::actingAs($this->employee);
    Carbon::setTestNow(Carbon::today()->setTime(7, 45, 0));

    $this->postJson('/api/v1/attendance/check-in', [
        'office_location_id' => $this->office->id,
        'latitude' => -6.175500,
        'longitude' => 106.827000,
        'device_name' => 'iPhone 15 Pro',
    ])->assertStatus(201);

    Carbon::setTestNow(Carbon::today()->setTime(17, 15, 0));

    $response = $this->postJson('/api/v1/attendance/check-out', [
        'latitude' => -6.175520,
        'longitude' => 106.827050,
        'device_name' => 'iPhone 15 Pro',
        'notes' => 'Pulang kerja',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.check_out_device_name', 'iPhone 15 Pro');

    expect($response->json('data.check_out_at'))->not->toBeNull();
});

test('employee cannot check-out without check-in first', function () {
    Sanctum::actingAs($this->employee);

    $response = $this->postJson('/api/v1/attendance/check-out', [
        'latitude' => -6.175520,
        'longitude' => 106.827050,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['attendance']);
});

test('employee can get today attendance status', function () {
    Sanctum::actingAs($this->employee);

    // Saat belum check-in
    $responseBefore = $this->getJson('/api/v1/attendance/today');
    $responseBefore->assertOk()
        ->assertJson([
            'has_checked_in' => false,
            'has_checked_out' => false,
            'data' => null,
        ]);

    // Setelah check-in
    Attendance::factory()->create([
        'user_id' => $this->employee->id,
        'office_location_id' => $this->office->id,
        'date' => Carbon::today()->toDateString(),
        'check_in_at' => Carbon::today()->setTime(7, 50, 0),
        'check_out_at' => null,
    ]);

    $responseAfter = $this->getJson('/api/v1/attendance/today');
    $responseAfter->assertOk()
        ->assertJson([
            'has_checked_in' => true,
            'has_checked_out' => false,
        ]);

    expect($responseAfter->json('data.id'))->not->toBeNull();
});

test('employee can get paginated attendance history', function () {
    Sanctum::actingAs($this->employee);

    // Buat 3 catatan absensi
    for ($i = 1; $i <= 3; $i++) {
        Attendance::factory()->create([
            'user_id' => $this->employee->id,
            'office_location_id' => $this->office->id,
            'date' => Carbon::today()->subDays($i)->toDateString(),
        ]);
    }

    $response = $this->getJson('/api/v1/attendance/history?per_page=10');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'date', 'status', 'device_name', 'distance_meters'],
            ],
            'links',
            'meta',
        ]);
});
