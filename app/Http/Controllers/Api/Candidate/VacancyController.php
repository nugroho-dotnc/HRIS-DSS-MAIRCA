<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VacancyController extends Controller
{
    #[OA\Get(
        path: '/vacancies',
        summary: 'Mendapatkan daftar lowongan pekerjaan',
        description: 'List semua vacancy yang berstatus open (PUBLIC, tanpa autentikasi).',
        tags: ['Public - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'department_id', in: 'query', required: false, description: 'Filter berdasarkan departemen', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Pencarian berdasarkan judul atau deskripsi', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman', schema: new OA\Schema(type: 'integer', default: 10))
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
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Vacancies::with(['position.department'])
            ->where('status', 'open')
            ->where('deadline', '>=', now()->toDateString());

        if ($request->has('department_id')) {
            $query->whereHas('position', fn($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $vacancies = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Daftar lowongan berhasil diambil.',
            'data' => $vacancies,
        ]);
    }

    #[OA\Get(
        path: '/vacancies/{id}',
        summary: 'Melihat detail lowongan pekerjaan',
        description: 'Detail vacancy (PUBLIC, tanpa autentikasi). Menampilkan deskripsi, requirements, departemen, dan deadline.',
        tags: ['Public - Vacancies'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lowongan pekerjaan', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail lowongan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'title', type: 'string', example: 'Senior Software Engineer'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'requirements', type: 'string'),
                                new OA\Property(property: 'position', type: 'string', example: 'Backend Developer'),
                                new OA\Property(property: 'department', type: 'string', example: 'IT'),
                                new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2024-12-31'),
                                new OA\Property(property: 'status', type: 'string', example: 'open'),
                                new OA\Property(property: 'posted_at', type: 'string', format: 'date', example: '2024-01-01')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Lowongan tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $vacancy = Vacancies::with(['position.department'])
            ->where('status', 'open')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail lowongan berhasil diambil.',
            'data' => [
                'id' => $vacancy->id,
                'title' => $vacancy->title,
                'description' => $vacancy->description,
                'requirements' => $vacancy->requirements,
                'position' => $vacancy->position->position_name,
                'department' => $vacancy->position->department->department_name,
                'deadline' => $vacancy->deadline,
                'status' => $vacancy->status,
                'posted_at' => $vacancy->created_at->toDateString(),
            ],
        ]);
    }
}
