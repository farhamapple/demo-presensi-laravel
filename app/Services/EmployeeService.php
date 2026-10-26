<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    /**
     * Get paginated employees list with optional search and filters.
     */
    public function getEmployees(
        ?string $search = null,
        ?bool $isActive = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = User::query()
            ->where('role', UserRole::Pegawai);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        return $query->orderBy('name')
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * Find employee by ID.
     */
    public function findById(int $id): ?User
    {
        return User::where('role', UserRole::Pegawai)->find($id);
    }

    /**
     * Create a new employee.
     *
     * @param  array{name: string, email: string, nip?: ?string, phone?: ?string, password?: ?string, is_active?: bool}  $data
     *
     * @throws ValidationException
     */
    public function createEmployee(array $data): User
    {
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Alamat email sudah terdaftar.'],
            ]);
        }

        if (! empty($data['nip']) && User::where('nip', $data['nip'])->exists()) {
            throw ValidationException::withMessages([
                'nip' => ['NIP sudah terdaftar.'],
            ]);
        }

        $password = ! empty($data['password'])
            ? Hash::make($data['password'])
            : Hash::make('password123');

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'nip' => $data['nip'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => UserRole::Pegawai,
            'is_active' => $data['is_active'] ?? true,
            'password' => $password,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Update an employee.
     *
     * @param  array{name?: string, email?: string, nip?: ?string, phone?: ?string, password?: ?string, is_active?: bool}  $data
     *
     * @throws ValidationException
     */
    public function updateEmployee(User $user, array $data): User
    {
        if (isset($data['email']) && $data['email'] !== $user->email) {
            if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['Alamat email sudah digunakan oleh pengguna lain.'],
                ]);
            }
        }

        if (isset($data['nip']) && $data['nip'] !== $user->nip && ! empty($data['nip'])) {
            if (User::where('nip', $data['nip'])->where('id', '!=', $user->id)->exists()) {
                throw ValidationException::withMessages([
                    'nip' => ['NIP sudah digunakan oleh pegawai lain.'],
                ]);
            }
        }

        $updateData = [];

        foreach (['name', 'email', 'nip', 'phone', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (! empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Toggle employee active status.
     */
    public function toggleActiveStatus(User $user): User
    {
        $user->update(['is_active' => ! $user->is_active]);

        return $user->fresh();
    }

    /**
     * Reset employee password.
     */
    public function resetPassword(User $user, string $newPassword): User
    {
        $user->update(['password' => Hash::make($newPassword)]);

        return $user->fresh();
    }

    /**
     * Delete an employee.
     */
    public function deleteEmployee(User $user): bool
    {
        return (bool) $user->delete();
    }
}
