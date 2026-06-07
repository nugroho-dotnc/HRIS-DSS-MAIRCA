<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PositionController extends Controller
{
    #[OA\Get(
        path: '/admin/positions',
        summary: 'Mendapatkan daftar posisi',
        description: 'Mengambil semua data posisi beserta departemennya tanpa pagination, dan menyertakan total kriteria serta bobotnya.',
        security: [['sanctum' => []]],
        tags: ['Admin - Positions'],
        parameters: [
            new OA\Parameter(name: 'department_id', in: 'query', required: false, description: 'Filter berdasarkan ID departemen', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'is_active', in: 'query', required: false, description: 'Filter berdasarkan status aktif (true/false)', schema: new OA\Schema(type: 'boolean'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar posisi berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Daftar posisi berhasil diambil.'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                allOf: [
                                    new OA\Schema(ref: '#/components/schemas/Position'),
                                    new OA\Schema(
                                        properties: [
                                            new OA\Property(property: 'total_criteria', type: 'integer', example: 5),
                                            new OA\Property(property: 'total_weight', type: 'number', format: 'float', example: 100)
                                        ]
                                    )
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Position::with('department')
            ->withCount('recruitment_criteria')
            ->withSum('recruitment_criteria', 'weight');

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $positions = $query->orderBy('position_name')->get()->map(function ($position) {
            $data = $position->toArray();
            $data['total_criteria'] = $position->recruitment_criteria_count;
            $data['total_weight'] = (float) ($position->recruitment_criteria_sum_weight ?? 0);
            unset($data['recruitment_criteria_count'], $data['recruitment_criteria_sum_weight']);
            return $data;
        });
        if ($positions->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data tidak ditemukan.',
                'data'    => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar posisi berhasil diambil.',
            'data' => $positions,
        ]);
    }

    #[OA\Post(
        path: '/admin/positions',
        summary: 'Membuat posisi baru',
        description: 'Menambahkan posisi baru ke dalam departemen tertentu.',
        security: [['sanctum' => []]],
        tags: ['Admin - Positions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['department_id', 'position_name'],
                properties: [
                    new OA\Property(property: 'department_id', type: 'integer', example: 1),
                    new OA\Property(property: 'position_name', type: 'string', example: 'Software Engineer'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Posisi berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Posisi berhasil dibuat.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Position')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        // Cek unique per departemen
        $exists = Position::where('department_id', $request->department_id)
            ->where('position_name', $request->position_name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Posisi dengan nama yang sama sudah ada di departemen ini.',
            ], 422);
        }

        $position = Position::create([
            'department_id' => $request->department_id,
            'position_name' => $request->position_name,
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Posisi berhasil dibuat.',
            'data' => $position->load('department'),
        ], 201);
    }

    #[OA\Get(
        path: '/admin/positions/{id}',
        summary: 'Melihat detail posisi',
        description: 'Mengambil data detail posisi beserta kriteria rekrutmennya.',
        security: [['sanctum' => []]],
        tags: ['Admin - Positions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID posisi', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data posisi berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Position')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Posisi tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $position = Position::with(['department', 'recruitment_criteria.likertScales'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail posisi berhasil diambil.',
            'data' => $position,
        ]);
    }

    #[OA\Put(
        path: '/admin/positions/{id}',
        summary: 'Update posisi',
        description: 'Memperbarui data posisi berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Positions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID posisi', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'department_id', type: 'integer', example: 2),
                    new OA\Property(property: 'position_name', type: 'string', example: 'Senior Software Engineer'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Posisi berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Posisi berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Position')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Posisi tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $position = Position::findOrFail($id);

        $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'position_name' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $position->fill($request->only(['department_id', 'position_name', 'is_active']));
        $position->save();

        return response()->json([
            'success' => true,
            'message' => 'Posisi berhasil diupdate.',
            'data' => $position->load('department'),
        ]);
    }

    #[OA\Delete(
        path: '/admin/positions/{id}',
        summary: 'Hapus posisi',
        description: 'Menghapus posisi berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Positions'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID posisi', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Posisi berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Posisi berhasil dihapus.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Posisi tidak ditemukan'),
            new OA\Response(response: 422, description: 'Posisi masih memiliki lowongan aktif', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $position = Position::findOrFail($id);

        if ($position->vacancies()->where('status', 'open')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Posisi tidak dapat dihapus karena masih ada lowongan aktif.',
                'data' => null,
            ], 422);
        }

        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'Posisi berhasil dihapus.',
            'data' => null,
        ]);
    }
}
