<?php

namespace App\Filament\Resources\OfficeLocations\Pages;

use App\Filament\Resources\OfficeLocations\OfficeLocationResource;
use App\Filament\Resources\OfficeLocations\Schemas\OfficeLocationForm;
use App\Services\GeofencingService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateOfficeLocation extends CreateRecord
{
    protected static string $resource = OfficeLocationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['polygon_coordinates']) && is_string($data['polygon_coordinates'])) {
            $decoded = json_decode($data['polygon_coordinates'], true);
            if (is_array($decoded)) {
                $data['polygon_coordinates'] = $decoded;
            }
        }

        if (isset($data['location']['geojson'])) {
            $polygon = OfficeLocationForm::extractPolygonFromGeoJson($data['location']['geojson']);
            if ($polygon) {
                $data['polygon_coordinates'] = $polygon;
            }

            $circle = OfficeLocationForm::extractCircleFromGeoJson($data['location']['geojson']);
            if ($circle) {
                $data['radius_meters'] = round($circle['radius']);
                $data['latitude'] = round($circle['lat'], 7);
                $data['longitude'] = round($circle['lng'], 7);
                if (empty($data['polygon_coordinates'])) {
                    $data['polygon_coordinates'] = OfficeLocationForm::circleToPolygon($circle['lat'], $circle['lng'], $circle['radius']);
                }
            }
        }

        if (empty($data['polygon_coordinates']) || count($data['polygon_coordinates']) < 3) {
            throw ValidationException::withMessages([
                'location' => 'Harap gambar batas area (polygon, rectangle, atau radius) kantor pada peta.',
            ]);
        }

        if (empty($data['latitude']) || empty($data['longitude'])) {
            $centroid = app(GeofencingService::class)->calculateCentroid($data['polygon_coordinates']);
            $data['latitude'] = $centroid['lat'];
            $data['longitude'] = $centroid['lng'];
        }
        $data['radius_meters'] ??= 100;

        unset($data['location'], $data['polygon_points_count']);

        return $data;
    }
}
