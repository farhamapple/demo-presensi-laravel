<?php

namespace App\Http\Resources\Api;

use App\Models\OfficeLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OfficeLocation
 */
class OfficeLocationApiResource extends JsonResource
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
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius_meters' => $this->radius_meters,
            'polygon_coordinates' => $this->polygon_coordinates ?? [],
            'is_active' => $this->is_active,
        ];
    }
}
