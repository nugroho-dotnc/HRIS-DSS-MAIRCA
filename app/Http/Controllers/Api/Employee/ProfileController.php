<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/employee/profile',
        summary: 'Melihat profil pribadi',
        description: 'Lihat data profil pribadi dan kepegawaian employee yang sedang login.',
        security: [['sanctum' => []]],
        tags: ['Employee - Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'employment', type: 'object')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee()->with([
            'department',
            'position',
            'supervisor.user',
        ])->first();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'role', 'status']),
                'employment' => $employee,
            ],
        ]);
    }

    #[OA\Put(
        path: '/employee/profile',
        summary: 'Update profil pribadi',
        description: 'Update data pribadi employee seperti nama dan nomor telepon. Data kepegawaian tidak dapat diubah.',
        security: [['sanctum' => []]],
        tags: ['Employee - Profile'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso Update'),
                    new OA\Property(property: 'phone', type: 'string', example: '081234567899')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Profil berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate.',
            'data' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    #[OA\Get(
        path: '/employee/employment',
        summary: 'Melihat data kepegawaian',
        description: 'Lihat data kepegawaian secara terpisah yang bersifat read-only.',
        security: [['sanctum' => []]],
        tags: ['Employee - Profile'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data kepegawaian berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'employee_id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'email', type: 'string', example: 'budi@example.com'),
                                new OA\Property(property: 'department', type: 'string', example: 'IT'),
                                new OA\Property(property: 'position', type: 'string', example: 'Backend Developer'),
                                new OA\Property(property: 'supervisor', type: 'string', example: 'Joko Manager'),
                                new OA\Property(property: 'join_date', type: 'string', format: 'date', example: '2023-01-01'),
                                new OA\Property(property: 'contract_status', type: 'string', example: 'permanent'),
                                new OA\Property(property: 'note', type: 'string')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Data kepegawaian belum tersedia', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function employment(Request $request): JsonResponse
    {
        $user = $request->user();
        $employee = $user->employee()->with([
            'department',
            'position.recruitment_criteria',
            'supervisor.user',
            'subordinates',
        ])->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data kepegawaian belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employee_id' => $employee->id,
                'name' => $user->name,
                'email' => $user->email,
                'department' => $employee->department->department_name,
                'position' => $employee->position->position_name,
                'supervisor' => $employee->supervisor ? $employee->supervisor->user->name : null,
                'join_date' => $employee->join_date,
                'contract_status' => $employee->contract_status,
                'note' => 'Data kepegawaian bersifat read-only. Hubungi HR untuk perubahan.',
            ],
        ]);
    }
}
