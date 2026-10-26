<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate an employee via email or NIP and issue a Sanctum token.
     *
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function loginPegawai(string $identifier, string $password, ?string $deviceName = null): array
    {
        $user = User::query()
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier)
                    ->orWhere('nip', $identifier);
            })
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Kredensial login (Email/NIP atau kata sandi) tidak valid.'],
            ]);
        }

        if ($user->role !== UserRole::Pegawai) {
            throw ValidationException::withMessages([
                'role' => ['Akun ini bukan akun pegawai mobile.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'is_active' => ['Akun pegawai Anda sedang dinonaktifkan. Silakan hubungi Admin SDM.'],
            ]);
        }

        $tokenName = $deviceName ? "mobile-{$deviceName}" : 'mobile-smartphone';
        $token = $user->createToken($tokenName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revoke current token for employee.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
