<?php

namespace App\Filament\Resources\OfficeLocations\Tables;

use App\Models\OfficeLocation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class OfficeLocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(35)
                    ->searchable(),

                TextColumn::make('coordinate_center')
                    ->label('Koordinat Pusat')
                    ->state(fn (OfficeLocation $record): string => ($record->latitude !== null && $record->longitude !== null)
                        ? "{$record->latitude}, {$record->longitude}"
                        : '-'
                    )
                    ->copyable(),

                TextColumn::make('radius_meters')
                    ->label('Radius')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} m" : '-')
                    ->sortable(),

                TextColumn::make('polygon_vertices')
                    ->label('Polygon')
                    ->state(fn (OfficeLocation $record): string => ! empty($record->polygon_coordinates)
                        ? count($record->polygon_coordinates).' titik'
                        : '-'
                    )
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Lokasi')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
