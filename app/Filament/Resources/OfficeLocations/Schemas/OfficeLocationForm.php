<?php

namespace App\Filament\Resources\OfficeLocations\Schemas;

use App\Models\OfficeLocation;
use App\Services\GeofencingService;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OfficeLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lokasi Kantor')
                    ->description('Masukkan identitas dan alamat resmi titik kantor yang divalidasi untuk presensi.')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('name')
                                ->label('Nama Lokasi Kantor')
                                ->placeholder('Contoh: Kantor Pusat Bappenas Jakarta')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(2),

                            Toggle::make('is_active')
                                ->label('Status Aktif')
                                ->helperText('Hanya lokasi aktif yang divalidasi sistem presensi.')
                                ->default(true)
                                ->inline(false),
                        ]),

                        Textarea::make('address')
                            ->label('Alamat Lengkap Kantor')
                            ->rows(2)
                            ->placeholder('Contoh: Jl. Taman Suropati No.2, RT.5/RW.5, Menteng, Kec. Menteng, Kota Jakarta Pusat')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pembuatan Boundary Area Presensi (Polygon Geofence)')
                    ->description('Gambar batas perimeter area kantor langsung pada peta menggunakan alat Polygon di bilah alat kiri atas.')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Placeholder::make('polygon_instructions')
                            ->label('Panduan Menggambar Boundary Polygon di Peta')
                            ->content(new HtmlString('
                                <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                                    <p>1. Klik ikon <strong>Polygon</strong> pada toolbar kiri atas peta.</p>
                                    <p>2. Klik pada peta untuk setiap titik sudut perimeter yang mengelilingi area kantor.</p>
                                    <p>3. Hubungkan kembali ke titik awal untuk mengunci dan menutup polygon.</p>
                                    <p>4. Gunakan ikon <strong>Edit Layers</strong> untuk memindahkan titik sudut, atau <strong>Drag Layers</strong> untuk menggeser polygon.</p>
                                </div>
                            '))
                            ->columnSpanFull(),

                        Map::make('location')
                            ->label('Peta Boundary Polygon Kantor (OpenStreetMap)')
                            ->columnSpanFull()
                            ->defaultLocation(latitude: -6.2018, longitude: 106.8329)
                            ->zoom(16)
                            ->clickable(false)
                            ->draggable(true)
                            ->showMarker(false)
                            ->showZoomControl(true)
                            ->showFullscreenControl(true)
                            ->showMyLocationButton(true)
                            ->geoMan(true)
                            ->geoManEditable(true)
                            ->geoManPosition('topleft')
                            ->drawPolygon(true)
                            ->drawRectangle(true)
                            ->drawMarker(false)
                            ->drawCircle(false)
                            ->drawCircleMarker(false)
                            ->drawPolyline(false)
                            ->drawText(false)
                            ->editPolygon(true)
                            ->deleteLayer(true)
                            ->dragMode(true)
                            ->cutPolygon(true)
                            ->rotateMode(false)
                            ->snappable(true)
                            ->setColor('#002d62')
                            ->setFilledColor('#0284c7')
                            ->extraStyles([
                                'min-height: 500px',
                                'border-radius: 0.75rem',
                                'overflow: hidden',
                                'border: 1px solid #cbd5e1',
                            ])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if ($state && isset($state['geojson'])) {
                                    $polygon = self::extractPolygonFromGeoJson($state['geojson']);
                                    if ($polygon) {
                                        $set('polygon_coordinates', $polygon);
                                        $centroid = app(GeofencingService::class)->calculateCentroid($polygon);
                                        $set('latitude', $centroid['lat']);
                                        $set('longitude', $centroid['lng']);
                                    }
                                }
                            })
                            ->afterStateHydrated(function (Set $set, ?OfficeLocation $record): void {
                                if ($record && ! empty($record->polygon_coordinates)) {
                                    $set('location', [
                                        'geojson' => self::polygonToGeoJson($record->polygon_coordinates),
                                    ]);
                                    $set('polygon_coordinates', $record->polygon_coordinates);
                                    if ($record->latitude !== null) {
                                        $set('latitude', (float) $record->latitude);
                                    }
                                    if ($record->longitude !== null) {
                                        $set('longitude', (float) $record->longitude);
                                    }
                                }
                            })
                            ->dehydrated(false),

                        Hidden::make('polygon_coordinates')
                            ->default([]),

                        Hidden::make('latitude'),
                        Hidden::make('longitude'),
                        Hidden::make('radius_meters')->default(100),
                    ]),
            ]);
    }

    /**
     * Extract polygon vertices [ ['lat' => ..., 'lng' => ...], ... ] from GeoJSON.
     *
     * @return array<int, array{lat: float, lng: float}>|null
     */
    public static function extractPolygonFromGeoJson(mixed $geojson): ?array
    {
        if (is_string($geojson)) {
            $geojson = json_decode($geojson, true);
        }

        if (! is_array($geojson)) {
            return null;
        }

        $features = $geojson['features'] ?? [$geojson];

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? $feature;
            $type = $geometry['type'] ?? null;
            $coordinates = $geometry['coordinates'] ?? null;

            if ($type === 'Polygon' && is_array($coordinates) && ! empty($coordinates[0])) {
                $polygon = [];
                foreach ($coordinates[0] as $point) {
                    if (isset($point[0], $point[1]) && is_numeric($point[0]) && is_numeric($point[1])) {
                        $polygon[] = [
                            'lat' => round((float) $point[1], 7),
                            'lng' => round((float) $point[0], 7),
                        ];
                    }
                }

                // If closed ring (first == last), remove the redundant last point
                if (count($polygon) > 3) {
                    $first = $polygon[0];
                    $last = end($polygon);
                    if (abs($first['lat'] - $last['lat']) < 0.0000001 && abs($first['lng'] - $last['lng']) < 0.0000001) {
                        array_pop($polygon);
                    }
                }

                if (count($polygon) >= 3) {
                    return $polygon;
                }
            }
        }

        return null;
    }

    /**
     * Convert polygon vertices into GeoJSON FeatureCollection.
     *
     * @param  array<int, array<string, mixed>>|null  $polygon
     * @return array<string, mixed>|null
     */
    public static function polygonToGeoJson(?array $polygon): ?array
    {
        if (empty($polygon) || count($polygon) < 3) {
            return null;
        }

        $ring = [];
        foreach ($polygon as $point) {
            $lat = (float) ($point['lat'] ?? $point['latitude'] ?? 0);
            $lng = (float) ($point['lng'] ?? $point['longitude'] ?? 0);
            $ring[] = [$lng, $lat]; // GeoJSON format: [longitude, latitude]
        }

        if (! empty($ring)) {
            $ring[] = $ring[0];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => 'Boundary Area Kantor',
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$ring],
                    ],
                ],
            ],
        ];
    }
}
