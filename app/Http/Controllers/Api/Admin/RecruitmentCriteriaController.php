<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LikertScale;
use App\Models\RecruitmentCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class RecruitmentCriteriaController extends Controller
{
    #[OA\Get(
        path: '/admin/criteria',
        summary: 'Mendapatkan daftar kriteria MAIRCA',
        description: 'Mengambil semua data kriteria beserta skala Likert-nya.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'position_id', in: 'query', required: false, description: 'Filter berdasarkan ID posisi', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar kriteria berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/RecruitmentCriteria')),
                        new OA\Property(property: 'weight_check', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'number'))
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
        $query = RecruitmentCriteria::with(['position.department', 'likertScales']);

        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        $criteria = $query->orderBy('position_id')->orderBy('name')->get();

        // Validasi total bobot per posisi
        $grouped = $criteria->groupBy('position_id')->map(function ($group) {
            return $group->sum('weight');
        });
        if ($criteria->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data tidak ditemukan.',
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar kriteria berhasil diambil.',
            'data' => [
                'criteria' => $criteria,
                'weight_check' => $grouped, // Tampilkan total bobot per posisi untuk validasi
            ],
        ]);
    }

    #[OA\Get(
        path: '/admin/positions/{id}/criteria',
        summary: 'Mendapatkan daftar kriteria berdasarkan ID posisi',
        description: 'Mengambil daftar kriteria MAIRCA yang terhubung ke satu posisi tertentu.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID posisi', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar kriteria berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Daftar kriteria berhasil diambil.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'position', type: 'object'),
                                new OA\Property(property: 'criteria', type: 'array', items: new OA\Items(ref: '#/components/schemas/RecruitmentCriteria')),
                                new OA\Property(property: 'total_weight', type: 'number', example: 100)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Posisi tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function getByPosition(string $id): JsonResponse
    {
        $position = \App\Models\Position::findOrFail($id);

        $criteria = RecruitmentCriteria::with(['likertScales'])
            ->where('position_id', $id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar kriteria berdasarkan posisi berhasil diambil.',
            'data' => [
                'position' => [
                    'id' => $position->id,
                    'position_name' => $position->position_name,
                ],
                'criteria' => $criteria,
                'total_weight' => (float) $criteria->sum('weight'),
            ],
        ]);
    }

    #[OA\Post(
        path: '/admin/criteria',
        summary: 'Membuat kriteria MAIRCA baru',
        description: 'Menambahkan kriteria baru untuk suatu posisi.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['position_id', 'name', 'weight', 'type', 'data_type'],
                properties: [
                    new OA\Property(property: 'position_id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Kemampuan Komunikasi'),
                    new OA\Property(property: 'weight', type: 'number', example: 25.0),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'type', type: 'string', enum: ['benefit', 'cost'], example: 'benefit'),
                    new OA\Property(property: 'data_type', type: 'string', enum: ['kualitatif', 'kuantitatif'], example: 'kualitatif')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Kriteria berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Kriteria MAIRCA berhasil ditambahkan.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/RecruitmentCriteria')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal atau bobot melebihi 100%', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'name' => 'required|string|max:255',
            'weight' => 'required|numeric|min:0|max:1',
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(['benefit', 'cost'])],
            'data_type' => ['required', Rule::in(['kualitatif', 'kuantitatif'])],
        ]);

        // Validasi: total bobot per posisi tidak boleh melebihi 100%
        $existingWeight = RecruitmentCriteria::where('position_id', $request->position_id)
            ->sum('weight');

        if (($existingWeight + $request->weight) > 1) {
            return response()->json([
                'success' => false,
                'message' => 'Total bobot kriteria untuk posisi ini akan melebihi 100%. Sisa bobot yang tersedia: ' . (1 - $existingWeight) . '%.',
                'existing_weight' => $existingWeight,
                'available_weight' => 1 - $existingWeight,
                'data' => null,
            ], 422);
        }

        $criteria = RecruitmentCriteria::create($request->only([
            'position_id',
            'name',
            'weight',
            'description',
            'type',
            'data_type',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kriteria MAIRCA berhasil ditambahkan.',
            'data' => $criteria->load(['position', 'likertScales']),
        ], 201);
    }

    #[OA\Get(
        path: '/admin/criteria/{id}',
        summary: 'Melihat detail kriteria',
        description: 'Mengambil data detail kriteria berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data kriteria berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/RecruitmentCriteria')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Kriteria tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::with(['position.department', 'likertScales'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail kriteria berhasil diambil.',
            'data' => $criteria,
        ]);
    }

    #[OA\Put(
        path: '/admin/criteria/{id}',
        summary: 'Update kriteria',
        description: 'Memperbarui data kriteria berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Kemampuan Komunikasi Update'),
                    new OA\Property(property: 'weight', type: 'number', example: 30.0),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'type', type: 'string', enum: ['benefit', 'cost'], example: 'benefit'),
                    new OA\Property(property: 'data_type', type: 'string', enum: ['kualitatif', 'kuantitatif'], example: 'kualitatif')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kriteria berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Kriteria berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/RecruitmentCriteria')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Kriteria tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'weight' => 'sometimes|numeric|min:0|max:1',
            'description' => 'nullable|string',
            'type' => ['sometimes', Rule::in(['benefit', 'cost'])],
            'data_type' => ['sometimes', Rule::in(['kualitatif', 'kuantitatif'])],
        ]);

        if ($request->has('weight')) {
            // Cek total bobot (tidak termasuk kriteria ini sendiri)
            $otherWeight = RecruitmentCriteria::where('position_id', $criteria->position_id)
                ->where('id', '!=', $id)
                ->sum('weight');

            if (($otherWeight + $request->weight) > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total bobot kriteria akan melebihi 100%. Bobot dari kriteria lain: ' . $otherWeight . '%.',
                    'available_weight' => 1 - $otherWeight,
                    'data' => null,
                ], 422);
            }
        }

        $criteria->fill($request->only(['name', 'weight', 'description', 'type', 'data_type']));
        $criteria->save();

        return response()->json([
            'success' => true,
            'message' => 'Kriteria berhasil diupdate.',
            'data' => $criteria->load(['position', 'likertScales']),
        ]);
    }

    #[OA\Delete(
        path: '/admin/criteria/{id}',
        summary: 'Hapus kriteria',
        description: 'Menghapus kriteria berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kriteria berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Kriteria berhasil dihapus.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Kriteria tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);
        $criteria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kriteria berhasil dihapus.',
            'data' => null,
        ]);
    }

    // ─── Likert Scale Management ───────────────────────────────────────────────

    #[OA\Get(
        path: '/admin/criteria/{id}/likert',
        summary: 'Mendapatkan skala Likert untuk kriteria',
        description: 'Mengambil data skala Likert untuk kriteria tertentu.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skala Likert berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/LikertScale'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Kriteria bukan kualitatif', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 404, description: 'Kriteria tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function likertIndex(string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);

        if ($criteria->data_type !== 'kualitatif') {
            return response()->json([
                'success' => false,
                'message' => 'Kriteria ini bertipe kuantitatif, tidak memiliki skala Likert.',
                'data' => null,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar skala Likert berhasil diambil.',
            'data' => $criteria->likertScales()->orderBy('value')->get(),
        ]);
    }

    #[OA\Post(
        path: '/admin/criteria/{id}/likert',
        summary: 'Membuat opsi skala Likert baru',
        description: 'Menambahkan opsi skala Likert untuk kriteria kualitatif.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['label', 'value'],
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Sangat Baik'),
                    new OA\Property(property: 'value', type: 'number', example: 5.0)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Skala Likert berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Skala Likert berhasil ditambahkan.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/LikertScale')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal atau Kriteria bukan kualitatif', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 404, description: 'Kriteria tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function likertStore(Request $request, string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);

        if ($criteria->data_type !== 'kualitatif') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kriteria kualitatif yang dapat memiliki skala Likert.',
                'data' => null,
            ], 422);
        }

        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|numeric',
        ]);

        $scale = LikertScale::create([
            'recruitment_criterias_id' => $id,
            'label' => $request->label,
            'value' => $request->value,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skala Likert berhasil ditambahkan.',
            'data' => $scale,
        ], 201);
    }

    #[OA\Put(
        path: '/admin/criteria/{id}/likert/{scaleId}',
        summary: 'Update opsi skala Likert',
        description: 'Memperbarui opsi skala Likert berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'scaleId', in: 'path', required: true, description: 'ID skala Likert', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'label', type: 'string', example: 'Sangat Baik'),
                    new OA\Property(property: 'value', type: 'number', example: 5.0)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skala Likert berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Skala Likert berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/LikertScale')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 404, description: 'Kriteria atau Skala Likert tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function likertUpdate(Request $request, string $id, string $scaleId): JsonResponse
    {
        $scale = LikertScale::where('recruitment_criterias_id', $id)
            ->where('id', $scaleId)
            ->firstOrFail();

        $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'value' => 'sometimes|required|numeric',
        ]);

        $scale->fill($request->only(['label', 'value']));
        $scale->save();

        return response()->json([
            'success' => true,
            'message' => 'Skala Likert berhasil diupdate.',
            'data' => $scale,
        ]);
    }

    #[OA\Delete(
        path: '/admin/criteria/{id}/likert/{scaleId}',
        summary: 'Hapus opsi skala Likert',
        description: 'Menghapus opsi skala Likert berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Criteria'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID kriteria', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'scaleId', in: 'path', required: true, description: 'ID skala Likert', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skala Likert berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Skala Likert berhasil dihapus.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Kriteria atau Skala Likert tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function likertDestroy(string $id, string $scaleId): JsonResponse
    {
        $scale = LikertScale::where('recruitment_criterias_id', $id)
            ->where('id', $scaleId)
            ->firstOrFail();

        $scale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skala Likert berhasil dihapus.',
            'data' => null,
        ]);
    }
}
