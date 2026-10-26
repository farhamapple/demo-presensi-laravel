<?php

namespace App\Filament\Widgets;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestAttendanceWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected static ?string $heading = 'Aktivitas Absensi Terbaru Hari Ini';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $today = Carbon::today()->toDateString();

        return $table
            ->query(
                Attendance::query()
                    ->with(['user', 'officeLocation'])
                    ->whereDate('date', $today)
                    ->orderByDesc('check_in_at')
            )
            ->columns([
                TextColumn::make('check_in_at')
                    ->label('Waktu Masuk')
                    ->dateTime('H:i:s')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('user.nip')
                    ->label('NIP')
                    ->copyable()
                    ->searchable(),

                TextColumn::make('user.unit_kerja')
                    ->label('Unit Kerja')
                    ->limit(25)
                    ->toggleable(),

                TextColumn::make('officeLocation.name')
                    ->label('Lokasi Kantor')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('distance_meters')
                    ->label('Jarak')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format($state, 0).' m' : '-')
                    ->sortable(),

                TextColumn::make('device_name')
                    ->label('Perangkat')
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (AttendanceStatus $state): string => $state->color())
                    ->formatStateUsing(fn (AttendanceStatus $state): string => $state->label()),
            ])
            ->paginated([5, 10]);
    }
}
