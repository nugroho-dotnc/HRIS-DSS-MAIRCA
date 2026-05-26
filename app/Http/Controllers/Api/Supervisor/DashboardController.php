<?php

namespace App\Http\Controllers\Api\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/supervisor/profile',
        summary: 'Melihat profil supervisor',
        description: 'Data profil supervisor beserta employee record dan daftar subordinat.',
        security: [['sanctum' => []]],
        tags: ['Supervisor - Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil supervisor berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'employee', type: 'object'),
                                new OA\Property(property: 'team_size', type: 'integer', example: 5),
                                new OA\Property(property: 'scope_note', type: 'string')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Data employee belum tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
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

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data employee untuk akun supervisor ini belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'role', 'status']),
                'employee' => $employee,
                'team_size' => $employee->subordinates->count(),
                'scope_note' => 'Modul absensi, validasi kehadiran, dan evaluasi KPI akan tersedia pada versi berikutnya.',
            ],
        ]);
    }
}
