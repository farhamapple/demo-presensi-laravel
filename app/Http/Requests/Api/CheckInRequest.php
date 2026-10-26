<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'office_location_id' => ['required', 'integer', 'exists:office_locations,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'device_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'office_location_id.required' => 'Lokasi kantor wajib dipilih.',
            'office_location_id.exists' => 'Lokasi kantor tidak ditemukan.',
            'latitude.required' => 'Koordinat latitude GPS wajib dikirim.',
            'longitude.required' => 'Koordinat longitude GPS wajib dikirim.',
        ];
    }
}
