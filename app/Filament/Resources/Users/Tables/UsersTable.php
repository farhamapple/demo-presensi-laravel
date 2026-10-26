<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Alamat Email')
                    ->searchable(),

                TextColumn::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (UserRole $state): string => match ($state) {
                        UserRole::Admin => 'warning',
                        UserRole::Pegawai => 'info',
                    })
                    ->formatStateUsing(fn (UserRole $state): string => $state->label()),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Peran')
                    ->options(UserRole::class),

                SelectFilter::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->options(fn () => User::whereNotNull('unit_kerja')->where('unit_kerja', '!=', '')->distinct()->pluck('unit_kerja', 'unit_kerja')->toArray()),

                TernaryFilter::make('is_active')
                    ->label('Status Keaktifan')
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
