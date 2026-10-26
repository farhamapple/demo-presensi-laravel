<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfficeLocationApiResource;
use App\Services\OfficeLocationService;
use Illuminate\Http\JsonResponse;

class OfficeLocationController extends Controller
{
    public function __construct(
        protected OfficeLocationService $officeLocationService
    ) {}

    /**
     * Get list of active office locations with geofence data.
     */
    public function index(): JsonResponse
    {
        $locations = $this->officeLocationService->getActiveLocations();

        return response()->json([
            'message' => 'Daftar lokasi kantor aktif berhasil dimuat.',
            'data' => OfficeLocationApiResource::collection($locations),
        ], 200);
    }
}
