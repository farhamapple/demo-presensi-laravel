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
            $data['polygon_points_count'] = count($data['polygon_coordinates']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

        if (! empty($data['polygon_coordinates']) && count($data['polygon_coordinates']) >= 3) {
            $centroid = app(GeofencingService::class)->calculateCentroid($data['polygon_coordinates']);
            $data['latitude'] = $centroid['lat'];
            $data['longitude'] = $centroid['lng'];
        }

        $data['radius_meters'] ??= 100;

        unset($data['location'], $data['polygon_points_count']);

        return $data;
    }
}
