<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/supervisor/profile
     * Data profil supervisor beserta employee record dan daftar subordinat.
     *
     * Supervisor dalam scope versi ini memiliki akses terbatas —
     * modul absensi dan KPI akan diaktifkan pada scope berikutnya.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        $employee = $user->employee()->with([
            'department',
            'position',
            'supervisor.user',
            'subordinates.user',
            'subordinates.position',
            'subordinates.department',
        ])->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data employee untuk akun supervisor ini belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user'       => $user->only(['id', 'name', 'email', 'role', 'status']),
                'employee'   => $employee,
                'team_size'  => $employee->subordinates->count(),
                'scope_note' => 'Modul absensi, validasi kehadiran, dan evaluasi KPI akan tersedia pada versi berikutnya.',
            ],
        ]);
    }
}
