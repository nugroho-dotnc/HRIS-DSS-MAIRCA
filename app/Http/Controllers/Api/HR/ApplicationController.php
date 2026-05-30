<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ApplicationController extends Controller
{
    #[OA\Get(
        path: '/hr/applications',
        summary: 'Mendapatkan daftar lamaran',
        description: 'List semua lamaran masuk dengan filter status atau vacancy.',
        security: [['sanctum' => []]],
        tags: ['HR - Applications'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter berdasarkan status', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'vacancy_id', in: 'query', required: false, description: 'Filter berdasarkan ID lowongan', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Pencarian berdasarkan nama atau email kandidat', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar lamaran berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Application')),
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
        $query = Application::with([
            'candidate',
            'vacancy.position.department',
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('vacancy_id')) {
            $query->where('vacancy_id', $request->vacancy_id);
        }

        if ($request->has('search')) {
            $query->whereHas('candidate', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $applications = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar lamaran berhasil diambil.',
            'data' => $applications,
        ]);
    }

    #[OA\Get(
        path: '/hr/applications/{id}',
        summary: 'Melihat detail lamaran',
        description: 'Detail lamaran lengkap beserta data kandidat dan riwayat interview.',
        security: [['sanctum' => []]],
        tags: ['HR - Applications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lamaran', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data lamaran berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Application')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lamaran tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $application = Application::with([
            'candidate',
            'vacancy.position.department',
            'vacancy.position.recruitment_criteria.likertScales',
            'interviewSessions.interviewer',
            'interviewSessions.scores.criteria',
            'result',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail lamaran berhasil diambil.',
            'data' => $application,
        ]);
    }

    #[OA\Patch(
        path: '/hr/applications/{id}/screening',
        summary: 'Pindah status ke screening',
        description: 'Pindahkan lamaran ke status screening — HR mulai mereview. Hanya untuk status applied.',
        security: [['sanctum' => []]],
        tags: ['HR - Applications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lamaran', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status lamaran berhasil diupdate ke screening',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lamaran dipindahkan ke screening.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Application')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lamaran tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi status gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function moveToScreening(string $id): JsonResponse
    {
        $application = Application::findOrFail($id);

        if ($application->status !== 'applied') {
            return response()->json([
                'success' => false,
                'message' => "Hanya lamaran berstatus 'applied' yang dapat dipindahkan ke screening. Status saat ini: {$application->status}.",
                'data' => null,
            ], 422);
        }

        $application->status = 'screening';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Lamaran dipindahkan ke screening.',
            'data' => $application->load('candidate', 'vacancy'),
        ]);
    }

    #[OA\Patch(
        path: '/hr/applications/{id}/reject',
        summary: 'Tolak lamaran',
        description: 'Tolak lamaran di tahap manapun (applied / screening / interview_scheduled).',
        security: [['sanctum' => []]],
        tags: ['HR - Applications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID lamaran', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status lamaran berhasil diupdate ke rejected',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lamaran berhasil ditolak.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Application')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lamaran tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi status gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function reject(string $id): JsonResponse
    {
        $application = Application::findOrFail($id);

        $allowedStatuses = ['applied', 'screening', 'interview_scheduled'];

        if (!in_array($application->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => "Lamaran berstatus '{$application->status}' tidak dapat ditolak pada tahap ini.",
                'data' => null,
            ], 422);
        }

        $application->status = 'rejected';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil ditolak.',
            'data' => $application->load('candidate', 'vacancy'),
        ]);
    }
}
