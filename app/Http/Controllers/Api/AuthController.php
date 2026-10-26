<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\Api\UserApiResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Authenticate employee and return Sanctum Bearer token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginPegawai(
            identifier: $request->input('identifier'),
            password: $request->input('password'),
            deviceName: $request->input('device_name')
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => new UserApiResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
        ], 200);
    }

    /**
     * Revoke active employee access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout berhasil. Sesi token telah dicabut.',
        ], 200);
    }

    /**
     * Get authenticated employee profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserApiResource($request->user()),
        ], 200);
    }
}
