<?php

namespace App\Filament\Resources\OfficeLocations\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OfficeLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lokasi Kantor')
                    ->description('Masukkan identitas dan alamat titik absensi kantor.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lokasi Kantor')
                            ->placeholder('Contoh: Kantor Pusat Jakarta')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->rows(3)
                            ->placeholder('Masukkan alamat jalan, kelurahan, kecamatan, kota')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Hanya lokasi aktif yang akan digunakan untuk validasi presensi pegawai.')
                            ->default(true),
                    ]),

                Section::make('Titik Koordinat Pusat & Radius Geofence')
                    ->description('Tentukan koordinat titik pusat kantor dan jarak radius batas absensi yang diperbolehkan.')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('latitude')
                                ->label('Latitude')
                                ->placeholder('-6.175392')
                                ->numeric()
                                ->required(),

                            TextInput::make('longitude')
                                ->label('Longitude')
                                ->placeholder('106.827153')
                                ->numeric()
                                ->required(),

                            TextInput::make('radius_meters')
                                ->label('Radius Absensi')
                                ->numeric()
                                ->default(100)
                                ->suffix('meter')
                                ->required()
                                ->helperText('Radius toleransi kehadiran dari titik pusat.'),
                        ]),
                    ]),

                Section::make('Batas Area Polygon (Geofencing Khusus)')
                    ->description('Tentukan titik-titik koordinat sudut untuk membentuk polygon batas area kantor riil.')
                    ->collapsible()
                    ->schema([
                        Repeater::make('polygon_coordinates')
                            ->label('Titik-Titik Sudut Polygon')
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
                            ->helperText('Minimal 3 titik koordinat untuk membentuk area polygon tertutup.'),
                    ]),
            ]);
    }
}
