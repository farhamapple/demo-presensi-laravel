<?php

namespace App\Http\Resources\Api;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attendance
 */
class AttendanceApiResource extends JsonResource
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
            'user_id' => $this->user_id,
            'office_location_id' => $this->office_location_id,
            'office_location' => new OfficeLocationApiResource($this->whenLoaded('officeLocation')),
            'date' => $this->date?->format('Y-m-d'),
            'check_in_at' => $this->check_in_at?->toIso8601String(),
            'check_out_at' => $this->check_out_at?->toIso8601String(),
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'distance_meters' => $this->distance_meters,
            'status' => [
                'value' => $this->status?->value,
                'label' => $this->status?->label(),
                'color' => $this->status?->color(),
            ],
            'notes' => $this->notes,
            'device_name' => $this->device_name,
            'device_id' => $this->device_id,
            'ip_address' => $this->ip_address,
            'check_out_device_name' => $this->check_out_device_name,
        ];
    }
}
