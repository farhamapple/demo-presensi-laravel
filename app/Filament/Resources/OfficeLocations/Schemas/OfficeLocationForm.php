<?php

namespace App\Filament\Resources\OfficeLocations\Schemas;

use App\Models\OfficeLocation;
use App\Services\GeofencingService;
use Dotswan\MapPicker\Fields\Map;
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

                        Grid::make(3)->schema([
                            TextInput::make('latitude')->label('Latitude')->readOnly(),
                            TextInput::make('longitude')->label('Longitude')->readOnly(),
                            TextInput::make('radius_meters')->label('Radius (meter)')->suffix('m')->default(100),
                        ]),

                        Grid::make(4)->schema([
                            TextInput::make('polygon_coordinates')
                                ->label('Koordinat Polygon')
                                ->placeholder('Contoh: [{"lat": -6.175, "lng": 106.827}, ...]')
                                ->helperText('Otomatis terisi saat menggambar batas di peta.')
                                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_SLASHES) : $state)
                                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (json_decode($state, true) ?? $state) : $state)
                                ->readOnly()
                                ->columnSpan(3),

                            TextInput::make('polygon_points_count')
                                ->label('Jumlah Titik')
                                ->helperText('Total titik sudut polygon.')
                                ->placeholder('0')
                                ->default(0)
                                ->suffix('titik')
                                ->readOnly()
                                ->dehydrated(false)
                                ->formatStateUsing(function ($state, ?OfficeLocation $record) {
                                    if ($state !== null && $state !== '') {
                                        return $state;
                                    }

                                    if ($record && ! empty($record->polygon_coordinates) && is_array($record->polygon_coordinates)) {
                                        return count($record->polygon_coordinates);
                                    }

                                    return 0;
                                })
                                ->columnSpan(1),
                        ]),
                    ]),

                Section::make('Pembuatan Boundary Area Presensi (Polygon Geofence)')
                    ->description('Gambar batas perimeter area kantor langsung pada peta menggunakan alat Polygon, Rectangle, atau Circle di bilah alat.')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Placeholder::make('polygon_instructions')
                            ->label('Panduan Menggambar Boundary di Peta')
                            ->content(new HtmlString('
                                <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                                    <p>1. <strong>Polygon</strong>: Klik ikon Polygon, klik setiap titik sudut perimeter, lalu <strong>klik kembali titik awal</strong> untuk menutup.</p>
                                    <p>2. <strong>Rectangle / Square</strong>: Klik ikon Rectangle lalu tarik untuk membuat boundary berbentuk kotak.</p>
                                    <p>3. <strong>Circle / Radius</strong>: Klik ikon Circle lalu tarik radius dari titik pusat kantor.</p>
                                    <p>4. Gunakan ikon <strong>Edit Layers</strong> untuk memindahkan titik sudut, atau <strong>Drag Layers</strong> untuk menggeser shape.</p>
                                    <p class="text-sky-700 dark:text-sky-400 font-medium pt-1">Visual boundary: Area akan otomatis diberi garis batas navy dan isian biru transparan agar perimeter kantor terlihat jelas.</p>
                                </div>
                            '))
                            ->columnSpanFull(),

                        Map::make('location')
                            ->label('Peta Boundary Polygon Kantor')
                            ->columnSpanFull()
                            ->defaultLocation(latitude: -6.17565, longitude: 106.82725)
                            ->draggable(true)
                            ->zoom(15)
                            ->minZoom(0)
                            ->maxZoom(20)
                            ->showMarker(false)
                            ->showZoomControl(true)
                            ->showFullscreenControl(true)
                            ->showMyLocationButton(true)
                            ->geoMan(true)
                            ->geoManEditable(true)
                            ->geoManPosition('topleft')
                            ->drawPolygon(true)
                            ->drawRectangle(true)
                            ->drawCircle(true)
                            ->drawCircleMarker(false)
                            ->drawMarker(false)
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
                                'min-height: 480px',
                                'border-radius: 0.75rem',
                                'overflow: hidden',
                                'border: 1px solid #cbd5e1',
                            ])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if ($state && isset($state['geojson'])) {
                                    $polygon = self::extractPolygonFromGeoJson($state['geojson']);
                                    $circle = self::extractCircleFromGeoJson($state['geojson']);

                                    if ($polygon) {
                                        $set('polygon_coordinates', json_encode($polygon, JSON_UNESCAPED_SLASHES));
                                        $set('polygon_points_count', count($polygon));
                                        $centroid = app(GeofencingService::class)->calculateCentroid($polygon);
                                        $set('latitude', $centroid['lat']);
                                        $set('longitude', $centroid['lng']);
                                    } elseif ($circle) {
                                        $set('radius_meters', round($circle['radius']));
                                        $set('latitude', round($circle['lat'], 7));
                                        $set('longitude', round($circle['lng'], 7));
                                        $circlePolygon = self::circleToPolygon($circle['lat'], $circle['lng'], $circle['radius']);
                                        $set('polygon_coordinates', json_encode($circlePolygon, JSON_UNESCAPED_SLASHES));
                                        $set('polygon_points_count', count($circlePolygon));
                                    } else {
                                        $set('polygon_coordinates', null);
                                        $set('polygon_points_count', 0);
                                    }
                                }
                            })
                            ->afterStateHydrated(function (Set $set, ?OfficeLocation $record): void {
                                if ($record) {
                                    if (! empty($record->polygon_coordinates)) {
                                        $set('location', [
                                            'geojson' => self::polygonToGeoJson($record->polygon_coordinates),
                                        ]);
                                        $set('polygon_coordinates', json_encode($record->polygon_coordinates, JSON_UNESCAPED_SLASHES));
                                        $set('polygon_points_count', count($record->polygon_coordinates));
                                    }
                                    if ($record->latitude !== null) {
                                        $set('latitude', (float) $record->latitude);
                                    }
                                    if ($record->longitude !== null) {
                                        $set('longitude', (float) $record->longitude);
                                    }
                                    if ($record->radius_meters !== null) {
                                        $set('radius_meters', (int) $record->radius_meters);
                                    }
                                }
                            })
                            ->dehydrated(false),

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
     * Extract circle data [ 'lat' => ..., 'lng' => ..., 'radius' => ... ] from GeoJSON.
     *
     * @return array{lat: float, lng: float, radius: float}|null
     */
    public static function extractCircleFromGeoJson(mixed $geojson): ?array
    {
        if (is_string($geojson)) {
            $geojson = json_decode($geojson, true);
        }

        if (! is_array($geojson)) {
            return null;
        }

        $features = $geojson['features'] ?? [$geojson];

        foreach ($features as $feature) {
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? $feature;
            $type = $properties['type'] ?? null;
            $coordinates = $geometry['coordinates'] ?? null;

            if ($type === 'Circle' && isset($properties['radius']) && is_array($coordinates) && count($coordinates) >= 2) {
                return [
                    'lat' => (float) $coordinates[1],
                    'lng' => (float) $coordinates[0],
                    'radius' => (float) $properties['radius'],
                ];
            }
        }

        return null;
    }

    /**
     * Approximate a circle as a polygon ring with N vertices.
     *
     * @return array<int, array{lat: float, lng: float}>
     */
    public static function circleToPolygon(float $lat, float $lng, float $radiusMeters, int $points = 32): array
    {
        $polygon = [];
        $earthRadius = 6371000;

        for ($i = 0; $i < $points; $i++) {
            $bearing = deg2rad(($i * 360) / $points);
            $latRad = deg2rad($lat);
            $lngRad = deg2rad($lng);

            $newLatRad = asin(
                sin($latRad) * cos($radiusMeters / $earthRadius) +
                cos($latRad) * sin($radiusMeters / $earthRadius) * cos($bearing)
            );

            $newLngRad = $lngRad + atan2(
                sin($bearing) * sin($radiusMeters / $earthRadius) * cos($latRad),
                cos($radiusMeters / $earthRadius) - sin($latRad) * sin($newLatRad)
            );

            $polygon[] = [
                'lat' => round(rad2deg($newLatRad), 7),
                'lng' => round(rad2deg($newLngRad), 7),
            ];
        }

        return $polygon;
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
                        'color' => '#002d62',
                        'fillColor' => '#0284c7',
                        'weight' => 3,
                        'fillOpacity' => 0.35,
                        'stroke' => '#002d62',
                        'stroke-width' => 3,
                        'stroke-opacity' => 1,
                        'fill' => '#0284c7',
                        'fill-opacity' => 0.35,
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
