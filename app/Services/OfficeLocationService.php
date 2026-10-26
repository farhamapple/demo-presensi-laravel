<?php

namespace App\Services;

use App\Models\OfficeLocation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class OfficeLocationService
{
    public function __construct(
        protected GeofencingService $geofencingService
    ) {}

    /**
     * Get all office locations.
     *
     * @return Collection<int, OfficeLocation>
     */
    public function getAllLocations(): Collection
    {
        return OfficeLocation::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active office locations.
     *
     * @return Collection<int, OfficeLocation>
     */
    public function getActiveLocations(): Collection
    {
        return OfficeLocation::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Find an office location by ID.
     */
    public function findById(int $id): ?OfficeLocation
    {
        return OfficeLocation::find($id);
    }

    /**
     * Create a new office location.
     *
     * @param  array{name: string, address?: ?string, polygon_coordinates: array<int, mixed>, is_active?: bool}  $data
     *
     * @throws ValidationException
     */
    public function createLocation(array $data): OfficeLocation
    {
        if (! $this->geofencingService->validatePolygon($data['polygon_coordinates'] ?? [])) {
            throw ValidationException::withMessages([
                'polygon_coordinates' => ['Titik koordinat polygon harus terdiri dari minimal 3 koordinat valid.'],
            ]);
        }

        $data['polygon_coordinates'] = $this->geofencingService->normalizePolygon($data['polygon_coordinates']);

        return OfficeLocation::create($data);
    }

    /**
     * Update an existing office location.
     *
     * @param  array{name?: string, address?: ?string, polygon_coordinates?: array<int, mixed>, is_active?: bool}  $data
     *
     * @throws ValidationException
     */
    public function updateLocation(OfficeLocation $location, array $data): OfficeLocation
    {
        if (isset($data['polygon_coordinates'])) {
            if (! $this->geofencingService->validatePolygon($data['polygon_coordinates'])) {
                throw ValidationException::withMessages([
                    'polygon_coordinates' => ['Titik koordinat polygon harus terdiri dari minimal 3 koordinat valid.'],
                ]);
            }

            $data['polygon_coordinates'] = $this->geofencingService->normalizePolygon($data['polygon_coordinates']);
        }

        $location->update($data);

        return $location->fresh();
    }

    /**
     * Delete an office location.
     */
    public function deleteLocation(OfficeLocation $location): bool
    {
        return (bool) $location->delete();
    }

    /**
     * Find which active office location contains the given GPS coordinates.
     */
    public function findMatchingLocationForPoint(float $latitude, float $longitude): ?OfficeLocation
    {
        $locations = $this->getActiveLocations();

        foreach ($locations as $location) {
            $polygon = $location->polygon_coordinates ?? [];
            if ($this->geofencingService->isPointInPolygon($latitude, $longitude, $polygon)) {
                return $location;
            }
        }

        return null;
    }
}
