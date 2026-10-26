<?php

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuthService;
use App\Services\EmployeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->authService = app(AuthService::class);
    $this->employeeService = app(EmployeeService::class);
});

test('employee can login via email and receive token', function () {
    $employee = User::factory()->pegawai()->create([
        'email' => 'pegawai.test@kantor.com',
        'password' => Hash::make('secret123'),
        'is_active' => true,
    ]);

    $response = $this->authService->loginPegawai(
        identifier: 'pegawai.test@kantor.com',
        password: 'secret123',
        deviceName: 'Pixel 8'
    );

    expect($response['user']->id)->toBe($employee->id)
        ->and($response['token'])->toBeString()
        ->and($response['token'])->not->toBeEmpty();
});

test('employee can login via NIP and receive token', function () {
    $employee = User::factory()->pegawai()->create([
        'nip' => '199501012022011005',
        'password' => Hash::make('secret123'),
        'is_active' => true,
    ]);

    $response = $this->authService->loginPegawai(
        identifier: '199501012022011005',
        password: 'secret123'
    );

    expect($response['user']->id)->toBe($employee->id)
        ->and($response['token'])->toBeString();
});

test('inactive employee cannot login', function () {
    User::factory()->pegawai()->inactive()->create([
        'email' => 'inactive@kantor.com',
        'password' => Hash::make('secret123'),
    ]);

    $this->expectException(ValidationException::class);

    $this->authService->loginPegawai('inactive@kantor.com', 'secret123');
});

test('admin cannot login via employee mobile auth', function () {
    User::factory()->admin()->create([
        'email' => 'admin@kantor.com',
        'password' => Hash::make('secret123'),
    ]);

    $this->expectException(ValidationException::class);

    $this->authService->loginPegawai('admin@kantor.com', 'secret123');
});

test('employee service creates employee with hashed password and pegawai role', function () {
    $employee = $this->employeeService->createEmployee([
        'name' => 'Doni Kusuma',
        'email' => 'doni@kantor.com',
        'nip' => '199304102019031001',
        'phone' => '081234567890',
        'password' => 'password123',
    ]);

    expect($employee)->toBeInstanceOf(User::class)
        ->and($employee->role)->toBe(UserRole::Pegawai)
        ->and($employee->is_active)->toBeTrue()
        ->and(Hash::check('password123', $employee->password))->toBeTrue();
});

test('employee service rejects duplicate email or NIP', function () {
    $this->employeeService->createEmployee([
        'name' => 'First Employee',
        'email' => 'duplicate@kantor.com',
        'nip' => '199001012015011001',
    ]);

    $this->expectException(ValidationException::class);

    $this->employeeService->createEmployee([
        'name' => 'Second Employee',
        'email' => 'duplicate@kantor.com',
        'nip' => '199001012015011002',
    ]);
});
