<?php

namespace App\Filament\Resources\OfficeLocations\Pages;

use App\Filament\Resources\OfficeLocations\OfficeLocationResource;
use App\Filament\Resources\OfficeLocations\Schemas\OfficeLocationForm;
use App\Services\GeofencingService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOfficeLocation extends EditRecord
{
    protected static string $resource = OfficeLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['polygon_coordinates'])) {
            $data['location'] = [
                'geojson' => OfficeLocationForm::polygonToGeoJson($data['polygon_coordinates']),
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['location']['geojson'])) {
            $polygon = OfficeLocationForm::extractPolygonFromGeoJson($data['location']['geojson']);
            if ($polygon) {
                $data['polygon_coordinates'] = $polygon;
            }
        }

        if (! empty($data['polygon_coordinates']) && count($data['polygon_coordinates']) >= 3) {
            $centroid = app(GeofencingService::class)->calculateCentroid($data['polygon_coordinates']);
            $data['latitude'] = $centroid['lat'];
            $data['longitude'] = $centroid['lng'];
        }

        $data['radius_meters'] ??= 100;

        unset($data['location']);

        return $data;
    }
}
