<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckInRequest;
use App\Http\Requests\Api\CheckOutRequest;
use App\Http\Resources\Api\AttendanceApiResource;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Record employee check-in (Absen Masuk).
     */
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->recordCheckIn(
            user: $request->user(),
            officeLocationId: (int) $request->input('office_location_id'),
            latitude: (float) $request->input('latitude'),
            longitude: (float) $request->input('longitude'),
            deviceName: $request->input('device_name'),
            deviceId: $request->input('device_id'),
            notes: $request->input('notes'),
            ipAddress: $request->ip()
        );

        return response()->json([
            'message' => 'Presensi masuk berhasil dicatat.',
            'data' => new AttendanceApiResource($attendance->load('officeLocation')),
        ], 201);
    }

    /**
     * Record employee check-out (Absen Pulang).
     */
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->recordCheckOut(
            user: $request->user(),
            latitude: (float) $request->input('latitude'),
            longitude: (float) $request->input('longitude'),
            deviceName: $request->input('device_name'),
            notes: $request->input('notes')
        );

        return response()->json([
            'message' => 'Presensi pulang berhasil dicatat.',
            'data' => new AttendanceApiResource($attendance->load('officeLocation')),
        ], 200);
    }

    /**
     * Get employee's attendance status today.
     */
    public function today(Request $request): JsonResponse
    {
        $todayAttendance = $this->attendanceService->getTodayAttendance($request->user());

        return response()->json([
            'has_checked_in' => $todayAttendance !== null,
            'has_checked_out' => $todayAttendance?->check_out_at !== null,
            'data' => $todayAttendance ? new AttendanceApiResource($todayAttendance) : null,
        ], 200);
    }

    /**
     * Get paginated attendance history for the authenticated employee.
     */
    public function history(Request $request): AnonymousResourceCollection
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $perPage = (int) $request->query('per_page', 15);

        $attendances = $this->attendanceService->getEmployeeAttendanceHistory(
            user: $request->user(),
            startDate: $startDate,
            endDate: $endDate,
            perPage: min(100, max(1, $perPage))
        );

        return AttendanceApiResource::collection($attendances);
    }
}
