<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VacancyController extends Controller
{
    #[OA\Get(
        path: '/hr/vacancies',
        summary: 'Mendapatkan daftar lowongan',
        description: 'List semua vacancy (termasuk yang dibuat HR lain) beserta jumlah lamaran.',
        security: [['sanctum' => []]],
        tags: ['HR - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter berdasarkan status', schema: new OA\Schema(type: 'string', enum: ['open', 'closed'])),
            new OA\Parameter(name: 'position_id', in: 'query', required: false, description: 'Filter berdasarkan posisi', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'my_vacancies', in: 'query', required: false, description: 'Hanya tampilkan lowongan yang dibuat oleh HR saat ini', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar lowongan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Vacancy')),
                                new OA\Property(property: 'total', type: 'integer', example: 50)
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
    public function index(Request $request): JsonResponse
    {
        $query = Vacancies::with(['position.department', 'hr'])
            ->withCount('applications');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->has('my_vacancies') && $request->my_vacancies) {
            $query->where('hr_id', $request->user()->id);
        }

        $vacancies = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $vacancies,
        ]);
    }

    #[OA\Post(
        path: '/hr/vacancies',
        summary: 'Membuat lowongan baru',
        description: 'Buat lowongan pekerjaan baru.',
        security: [['sanctum' => []]],
        tags: ['HR - Vacancies'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['position_id', 'title', 'description', 'requirements', 'deadline'],
                properties: [
                    new OA\Property(property: 'position_id', type: 'integer', example: 1),
                    new OA\Property(property: 'title', type: 'string', example: 'Senior Software Engineer'),
                    new OA\Property(property: 'description', type: 'string', example: 'Deskripsi pekerjaan...'),
                    new OA\Property(property: 'requirements', type: 'string', example: 'Persyaratan...'),
                    new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2024-12-31')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Lowongan berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lowongan berhasil dibuat.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Vacancy')
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
            'position_id' => 'required|exists:positions,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'deadline' => 'required|date|after:today',
        ]);

        $vacancy = Vacancies::create([
            'hr_id' => $request->user()->id,
            'position_id' => $request->position_id,
            'title' => $request->title,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'deadline' => $request->deadline,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat.',
            'data' => $vacancy->load(['position.department', 'hr']),
        ], 201);
    }

    #[OA\Get(
        path: '/hr/vacancies/{id}',
        summary: 'Melihat detail lowongan',
        description: 'Mengambil data detail lowongan berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['HR - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lowongan', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail lowongan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Vacancy')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lowongan tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $vacancy = Vacancies::with([
            'position.department',
            'position.recruitment_criteria.likertScales',
            'hr',
            'applications.candidate',
        ])->withCount('applications')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $vacancy,
        ]);
    }

    #[OA\Put(
        path: '/hr/vacancies/{id}',
        summary: 'Update lowongan',
        description: 'Update data lowongan. Lowongan yang sudah ditutup tidak dapat diubah.',
        security: [['sanctum' => []]],
        tags: ['HR - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lowongan', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Senior Software Engineer Update'),
                    new OA\Property(property: 'description', type: 'string', example: 'Deskripsi pekerjaan update...'),
                    new OA\Property(property: 'requirements', type: 'string', example: 'Persyaratan update...'),
                    new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2024-12-31'),
                    new OA\Property(property: 'position_id', type: 'integer', example: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lowongan berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lowongan berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Vacancy')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lowongan tidak ditemukan'),
            new OA\Response(response: 422, description: 'Lowongan sudah ditutup atau validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $vacancy = Vacancies::findOrFail($id);

        if ($vacancy->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan yang sudah ditutup tidak dapat diubah.',
            ], 422);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'requirements' => 'sometimes|string',
            'deadline' => 'sometimes|date|after:today',
            'position_id' => 'sometimes|exists:positions,id',
        ]);

        $vacancy->fill($request->only(['title', 'description', 'requirements', 'deadline', 'position_id']));
        $vacancy->save();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diupdate.',
            'data' => $vacancy->load(['position.department']),
        ]);
    }

    #[OA\Patch(
        path: '/hr/vacancies/{id}/close',
        summary: 'Tutup lowongan',
        description: 'Tutup lowongan ketika posisi sudah terisi atau batas waktu habis.',
        security: [['sanctum' => []]],
        tags: ['HR - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lowongan', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lowongan berhasil ditutup',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lowongan berhasil ditutup.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Vacancy')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lowongan tidak ditemukan'),
            new OA\Response(response: 422, description: 'Lowongan sudah ditutup', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function close(string $id): JsonResponse
    {
        $vacancy = Vacancies::findOrFail($id);

        if ($vacancy->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan sudah dalam status closed.',
            ], 422);
        }

        $vacancy->status = 'closed';
        $vacancy->save();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil ditutup.',
            'data' => $vacancy,
        ]);
    }

    #[OA\Delete(
        path: '/hr/vacancies/{id}',
        summary: 'Hapus lowongan',
        description: 'Menghapus lowongan. Lowongan tidak dapat dihapus jika sudah memiliki lamaran masuk.',
        security: [['sanctum' => []]],
        tags: ['HR - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lowongan', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lowongan berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lowongan berhasil dihapus.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lowongan tidak ditemukan'),
            new OA\Response(response: 422, description: 'Lowongan sudah memiliki lamaran', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $vacancy = Vacancies::findOrFail($id);

        if ($vacancy->applications()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak dapat dihapus karena sudah memiliki lamaran masuk.',
            ], 422);
        }

        $vacancy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus.',
        ]);
    }
}
