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
        if (isset($data['location']['geojson'])) {
            $polygon = OfficeLocationForm::extractPolygonFromGeoJson($data['location']['geojson']);
            if ($polygon) {
                $data['polygon_coordinates'] = $polygon;
            }
        }

        if (empty($data['polygon_coordinates']) || count($data['polygon_coordinates']) < 3) {
            throw ValidationException::withMessages([
                'location' => 'Harap gambar batas area polygon kantor pada peta (minimal 3 titik sudut).',
            ]);
        }

        $centroid = app(GeofencingService::class)->calculateCentroid($data['polygon_coordinates']);
        $data['latitude'] = $centroid['lat'];
        $data['longitude'] = $centroid['lng'];
        $data['radius_meters'] ??= 100;

        unset($data['location']);

        return $data;
    }
}
