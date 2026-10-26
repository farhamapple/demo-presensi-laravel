<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Standard work start time (HH:mm) for calculating late status.
     */
    protected string $workStartTime = '08:00';

    public function __construct(
        protected GeofencingService $geofencingService,
        protected OfficeLocationService $officeLocationService
    ) {}

    /**
     * Record employee check-in (Absen Masuk).
     *
     * @throws ValidationException
     */
    public function recordCheckIn(
        User $user,
        int $officeLocationId,
        float $latitude,
        float $longitude,
        ?string $deviceName = null,
        ?string $deviceId = null,
        ?string $notes = null
    ): Attendance {
        $today = Carbon::today()->toDateString();

        // 1. Check if user already checked in today
        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'attendance' => ['Anda sudah melakukan absen masuk untuk hari ini.'],
            ]);
        }

        // 2. Validate office location
        $officeLocation = $this->officeLocationService->findById($officeLocationId);

        if (! $officeLocation || ! $officeLocation->is_active) {
            throw ValidationException::withMessages([
                'office_location_id' => ['Lokasi kantor tidak ditemukan atau sedang tidak aktif.'],
            ]);
        }

        // 3. Validate GPS coordinates against office polygon geofence
        $isInsidePolygon = $this->geofencingService->isPointInPolygon(
            $latitude,
            $longitude,
            $officeLocation->polygon_coordinates ?? []
        );

        // 4. Determine attendance status
        $now = Carbon::now();
        $startTime = Carbon::today()->setTimeFromTimeString($this->workStartTime);

        if (! $isInsidePolygon) {
            $status = AttendanceStatus::OutsideArea;
            $distanceToCentroid = $this->calculateDistanceToCentroid($latitude, $longitude, $officeLocation->polygon_coordinates ?? []);
            $autoNote = sprintf('Absen di luar area kantor (jarak ~%s meter dari titik tengah kantor).', number_format($distanceToCentroid, 0));
            $notes = $notes ? $notes.' | '.$autoNote : $autoNote;
        } elseif ($now->gt($startTime)) {
            $status = AttendanceStatus::Late;
        } else {
            $status = AttendanceStatus::Present;
        }

        // 5. Create attendance record
        return Attendance::create([
            'user_id' => $user->id,
            'office_location_id' => $officeLocation->id,
            'date' => $today,
            'check_in_at' => $now,
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'status' => $status,
            'notes' => $notes,
            'device_name' => $deviceName,
            'device_id' => $deviceId,
        ]);
    }

    /**
     * Record employee check-out (Absen Pulang).
     *
     * @throws ValidationException
     */
    public function recordCheckOut(
        User $user,
        float $latitude,
        float $longitude,
        ?string $deviceName = null,
        ?string $notes = null
    ): Attendance {
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in_at) {
            throw ValidationException::withMessages([
                'attendance' => ['Belum ada catatan absen masuk untuk hari ini.'],
            ]);
        }

        if ($attendance->check_out_at) {
            throw ValidationException::withMessages([
                'attendance' => ['Anda sudah melakukan absen pulang untuk hari ini.'],
            ]);
        }

        $now = Carbon::now();

        $updateData = [
            'check_out_at' => $now,
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'check_out_device_name' => $deviceName ?? $attendance->device_name,
        ];

        if ($notes) {
            $updateData['notes'] = $attendance->notes
                ? $attendance->notes.' | Pulang: '.$notes
                : 'Pulang: '.$notes;
        }

        $attendance->update($updateData);

        return $attendance->fresh();
    }

    /**
     * Get employee's attendance record for today.
     */
    public function getTodayAttendance(User $user): ?Attendance
    {
        $today = Carbon::today()->toDateString();

        return Attendance::with('officeLocation')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
    }

    /**
     * Get paginated attendance history for a specific employee.
     */
    public function getEmployeeAttendanceHistory(
        User $user,
        ?string $startDate = null,
        ?string $endDate = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Attendance::with('officeLocation')
            ->where('user_id', $user->id);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        return $query->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get daily attendance summary statistics.
     *
     * @return array{total_attendance: int, present: int, late: int, outside_area: int}
     */
    public function getDailySummary(?string $date = null): array
    {
        $targetDate = $date ?? Carbon::today()->toDateString();

        $query = Attendance::whereDate('date', $targetDate);

        return [
            'total_attendance' => (clone $query)->count(),
            'present' => (clone $query)->where('status', AttendanceStatus::Present)->count(),
            'late' => (clone $query)->where('status', AttendanceStatus::Late)->count(),
            'outside_area' => (clone $query)->where('status', AttendanceStatus::OutsideArea)->count(),
        ];
    }

    /**
     * Set work start time string (e.g. '08:30').
     */
    public function setWorkStartTime(string $time): self
    {
        $this->workStartTime = $time;

        return $this;
    }

    /**
     * Helper to compute distance to office centroid in meters.
     */
    protected function calculateDistanceToCentroid(float $lat, float $lng, array $polygon): float
    {
        $centroid = $this->geofencingService->calculateCentroid($polygon);

        return $this->geofencingService->calculateDistanceInMeters(
            $lat,
            $lng,
            $centroid['lat'],
            $centroid['lng']
        );
    }
}
