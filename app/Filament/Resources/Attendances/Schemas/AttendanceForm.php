<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Enums\AttendanceStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Log Absensi')
                    ->description('Rincian transaksi data absensi pegawai.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('user_id')
                                ->label('Pegawai')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('office_location_id')
                                ->label('Lokasi Kantor')
                                ->relationship('officeLocation', 'name')
                                ->preload()
                                ->required(),

                            DatePicker::make('date')
                                ->label('Tanggal')
                                ->default(now())
                                ->required(),

                            Select::make('status')
                                ->label('Status Kehadiran')
                                ->options(AttendanceStatus::class)
                                ->required(),

                            DateTimePicker::make('check_in_at')
                                ->label('Waktu Masuk')
                                ->seconds(false),

                            DateTimePicker::make('check_out_at')
                                ->label('Waktu Pulang')
                                ->seconds(false),

                            TextInput::make('distance_meters')
                                ->label('Jarak dari Titik Kantor (meter)')
                                ->numeric()
                                ->suffix('m'),

                            TextInput::make('device_name')
                                ->label('Perangkat (Device)'),

                            TextInput::make('ip_address')
                                ->label('Alamat IP'),

                            TextInput::make('check_in_latitude')
                                ->label('Latitude Masuk')
                                ->numeric(),

                            TextInput::make('check_in_longitude')
                                ->label('Longitude Masuk')
                                ->numeric(),

                            Textarea::make('notes')
                                ->label('Catatan Keterangan')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
