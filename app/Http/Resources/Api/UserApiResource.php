<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'nip' => $this->nip,
            'unit_kerja' => $this->unit_kerja,
            'jabatan' => $this->jabatan,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,
            'role' => $this->role?->value,
            'is_active' => $this->is_active,
        ];
    }
}
