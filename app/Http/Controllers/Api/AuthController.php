<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login untuk semua role (admin, hr, supervisor, employee, candidate).
     * Mengembalikan Sanctum token.
     *
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Revoke semua token lama (opsional — single session)
        $user->tokens()->delete();

        $token = $user->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => '7 days',
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'status' => $user->status,
                ],
            ],
        ]);
    }

    /**
     * Register kandidat baru (PUBLIC — tanpa login).
     * Membuat user dengan role candidate.
     *
     * POST /api/auth/register
     */
    public function registerCandidate(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'candidate',
            'status'   => 'active',
        ]);

        $token = $user->createToken('api-token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi kandidat berhasil.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ],
        ], 201);
    }

    /**
     * Logout — revoke current token.
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Get data user yang sedang login.
     *
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = [
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'role'   => $user->role,
            'status' => $user->status,
        ];

        // Sertakan data tambahan sesuai role
        if (in_array($user->role, ['employee', 'supervisor'])) {
            $data['employee'] = $user->employee()->with(['department', 'position', 'supervisor.user'])->first();
        }

        if ($user->role === 'candidate') {
            $data['candidate_profile'] = Candidate::where('email', $user->email)->first();
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
