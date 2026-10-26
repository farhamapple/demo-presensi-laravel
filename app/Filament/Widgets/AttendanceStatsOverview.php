<?php

namespace App\Filament\Widgets;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $today = Carbon::today()->toDateString();

        $totalPegawai = User::where('role', UserRole::Pegawai)->where('is_active', true)->count();
        $sudahAbsen = Attendance::whereDate('date', $today)->whereNotNull('check_in_at')->count();
        $belumAbsen = max(0, $totalPegawai - $sudahAbsen);
        $terlambat = Attendance::whereDate('date', $today)->where('status', AttendanceStatus::Late)->count();
        $lokasiAktif = OfficeLocation::where('is_active', true)->count();

        return [
            Stat::make('Total Pegawai', (string) $totalPegawai)
                ->description('Pegawai aktif terdaftar')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->color('primary'),

            Stat::make('Sudah Absen', (string) $sudahAbsen)
                ->description('Presensi masuk hari ini')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Belum Absen', (string) $belumAbsen)
                ->description('Belum melakukan presensi')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color($belumAbsen > 0 ? 'warning' : 'gray'),

            Stat::make('Terlambat', (string) $terlambat)
                ->description('Lewat batas jam 08:00')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($terlambat > 0 ? 'danger' : 'gray'),

            Stat::make('Lokasi Aktif', (string) $lokasiAktif)
                ->description('Titik kantor geofence aktif')
                ->descriptionIcon(Heroicon::OutlinedMapPin)
                ->color('info'),
        ];
    }
}
