<?php

namespace App\Filament\Resources\OfficeLocations\Schemas;

use App\Models\OfficeLocation;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OfficeLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lokasi Kantor')
                    ->description('Masukkan identitas dan alamat resmi titik kantor yang diizinkan untuk presensi.')
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

                Section::make('Pemetaan Titik Pusat & Radius (Map Picker)')
                    ->description('Tentukan koordinat titik pusat kantor dengan mengklik peta atau menggeser marker. Radius absensi akan digambar otomatis di sekeliling titik.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Map::make('location')
                            ->label('Peta Interaktif Penentuan Titik Lokasi')
                            ->helperText('Klik pada peta atau geser marker pin untuk menentukan koordinat titik kantor. Lingkaran pada peta merepresentasikan radius toleransi kehadiran.')
                            ->columnSpanFull()
                            ->defaultLocation(latitude: -6.2018, longitude: 106.8329)
                            ->zoom(16)
                            ->clickable(true)
                            ->draggable(true)
                            ->showMarker(true)
                            ->showZoomControl(true)
                            ->showFullscreenControl(true)
                            ->showMyLocationButton(true)
                            ->rangeSelectField('radius_meters')
                            ->markerColor('#002d62')
                            ->extraStyles([
                                'min-height: 440px',
                                'border-radius: 0.75rem',
                                'overflow: hidden',
                                'border: 1px solid #cbd5e1',
                            ])
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                if ($state && isset($state['lat'], $state['lng'])) {
                                    $set('latitude', round((float) $state['lat'], 7));
                                    $set('longitude', round((float) $state['lng'], 7));
                                }
                            })
                            ->afterStateHydrated(function (Set $set, ?OfficeLocation $record): void {
                                if ($record && $record->latitude !== null && $record->longitude !== null) {
                                    $set('location', [
                                        'lat' => (float) $record->latitude,
                                        'lng' => (float) $record->longitude,
                                    ]);
                                }
                            })
                            ->dehydrated(false),

                        Grid::make(3)->schema([
                            TextInput::make('latitude')
                                ->label('Latitude Titik Pusat')
                                ->placeholder('-6.2018000')
                                ->numeric()
                                ->required()
                                ->default(-6.2018000)
                                ->live(onBlur: true)
                                ->helperText('Koordinat garis lintang titik kantor.')
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                    $lng = $get('longitude');
                                    if ($state !== null && $lng !== null) {
                                        $set('location', [
                                            'lat' => (float) $state,
                                            'lng' => (float) $lng,
                                        ]);
                                    }
                                }),

                            TextInput::make('longitude')
                                ->label('Longitude Titik Pusat')
                                ->placeholder('106.8329000')
                                ->numeric()
                                ->required()
                                ->default(106.8329000)
                                ->live(onBlur: true)
                                ->helperText('Koordinat garis bujur titik kantor.')
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                                    $lat = $get('latitude');
                                    if ($lat !== null && $state !== null) {
                                        $set('location', [
                                            'lat' => (float) $lat,
                                            'lng' => (float) $state,
                                        ]);
                                    }
                                }),

                            TextInput::make('radius_meters')
                                ->label('Radius Absensi')
                                ->numeric()
                                ->default(100)
                                ->minValue(10)
                                ->maxValue(5000)
                                ->suffix('meter')
                                ->required()
                                ->live(onBlur: true)
                                ->helperText('Batas jarak toleransi kehadiran pegawai dari titik pusat.'),
                        ]),
                    ]),

                Section::make('Batas Area Polygon Tambahan (Geofencing Khusus)')
                    ->description('Tentukan batas-batas sudut polygon untuk membatasi area fisik gedung kantor (Opsional jika ingin menerapkan batas perimeter presisi).')
                    ->icon('heroicon-o-variable')
                    ->collapsed(fn (?OfficeLocation $record): bool => empty($record?->polygon_coordinates))
                    ->collapsible()
                    ->schema([
                        Repeater::make('polygon_coordinates')
                            ->label('Koordinat Titik Sudut Perimeter Polygon')
                            ->schema([
                                TextInput::make('lat')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('lng')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('+ Tambah Titik Koordinat')
                            ->helperText('Minimal 3 titik sudut koordinat untuk membentuk batas poligon area tertutup.'),
                    ]),
            ]);
    }
}
