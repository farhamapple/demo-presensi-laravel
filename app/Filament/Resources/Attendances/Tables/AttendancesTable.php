<?php

namespace App\Filament\Resources\Attendances\Tables;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('check_in_at')
                    ->label('Waktu Masuk')
                    ->dateTime('H:i:s')
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('user.nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user.unit_kerja')
                    ->label('Unit Kerja')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('officeLocation.name')
                    ->label('Lokasi Kantor')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('distance_meters')
                    ->label('Jarak')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 0).' m' : '-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (AttendanceStatus $state): string => $state->color())
                    ->formatStateUsing(fn (AttendanceStatus $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('check_out_at')
                    ->label('Waktu Pulang')
                    ->dateTime('H:i:s')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('device_name')
                    ->label('Perangkat')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('Alamat IP')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Kehadiran')
                    ->options(AttendanceStatus::class),

                SelectFilter::make('office_location_id')
                    ->label('Lokasi Kantor')
                    ->relationship('officeLocation', 'name'),

                SelectFilter::make('unit_kerja')
                    ->label('Unit Kerja')
                    ->options(fn () => User::whereNotNull('unit_kerja')->where('unit_kerja', '!=', '')->distinct()->pluck('unit_kerja', 'unit_kerja')->toArray())
                    ->query(fn (Builder $query, array $data) => filled($data['value'])
                        ? $query->whereHas('user', fn ($q) => $q->where('unit_kerja', $data['value']))
                        : $query
                    ),

                Filter::make('date_range')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $q, $date): Builder => $q->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $q, $date): Builder => $q->whereDate('date', '<=', $date),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('detail')
                    ->label('Detail & Map')
                    ->icon(Heroicon::OutlinedMap)
                    ->color('info')
                    ->modalHeading(fn (Attendance $record): string => "Detail Transaksi Absensi: {$record->user->name}")
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn (Attendance $record) => view('filament.attendances.map-detail', ['record' => $record])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
