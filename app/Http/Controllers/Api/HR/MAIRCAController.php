<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\RecruitmentResult;
use App\Models\Vacancies;
use App\Services\MAIRCAService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MAIRCAController extends Controller
{
    public function __construct(protected MAIRCAService $maircaService)
    {
    }

    #[OA\Post(
        path: '/hr/mairca/calculate/{vacancyId}',
        summary: 'Kalkulasi MAIRCA',
        description: 'Hitung ranking MAIRCA untuk semua kandidat berstatus interview_done di vacancy ini. Hasil disimpan ke recruitment_results.',
        security: [['sanctum' => []]],
        tags: ['HR - MAIRCA'],
        parameters: [
            new OA\Parameter(name: 'vacancyId', in: 'path', required: true, description: 'ID lowongan pekerjaan', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kalkulasi berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Kalkulasi MAIRCA berhasil.'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal (misalnya kriteria atau skor tidak lengkap)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function calculate(string $vacancyId): JsonResponse
    {
        try {
            $result = $this->maircaService->calculate((int) $vacancyId);

            return response()->json([
                'success' => true,
                'message' => 'Kalkulasi MAIRCA berhasil.',
                'data' => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat kalkulasi MAIRCA.',
                'data' => ['error' => $e->getMessage()],
            ], 500);
        }
    }

    #[OA\Get(
        path: '/hr/mairca/ranking/{vacancyId}',
        summary: 'Melihat ranking MAIRCA',
        description: 'Lihat hasil ranking MAIRCA yang sudah terhitung. Menampilkan tabel perbandingan kandidat untuk HR sebagai basis keputusan.',
        security: [['sanctum' => []]],
        tags: ['HR - MAIRCA'],
        parameters: [
            new OA\Parameter(name: 'vacancyId', in: 'path', required: true, description: 'ID lowongan pekerjaan', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Ranking berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'vacancy', type: 'object'),
                                new OA\Property(property: 'total_candidates', type: 'integer', example: 5),
                                new OA\Property(property: 'ranking', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'note', type: 'string')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Belum ada hasil kalkulasi untuk vacancy ini'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function ranking(string $vacancyId): JsonResponse
    {
        $vacancy = Vacancies::with('position.department')->findOrFail($vacancyId);

        $results = RecruitmentResult::with([
            'application.candidate',
            'application.interviewSessions.scores.criteria',
        ])
            ->whereHas('application', fn($q) => $q->where('vacancy_id', $vacancyId))
            ->orderBy('ranking')
            ->get();

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada hasil kalkulasi MAIRCA untuk vacancy ini. Jalankan kalkulasi terlebih dahulu.',
                'data' => null,
            ], 404);
        }

        $formatted = $results->map(function ($result) {
            $app = $result->application;
            $candidate = $app->candidate;

            // Kumpulkan skor per kriteria
            $scores = [];
            foreach ($app->interviewSessions as $session) {
                foreach ($session->scores as $score) {
                    $scores[$score->criteria->name] = $score->score;
                }
            }

            return [
                'ranking' => $result->ranking,
                'application_id' => $app->id,
                'application_code' => $app->application_code,
                'candidate_name' => $candidate->name,
                'candidate_email' => $candidate->email,
                'final_score' => $result->final_score,  // total gap (terkecil = terbaik)
                'decission' => $result->decission,
                'scores' => $scores,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Ranking MAIRCA berhasil diambil.',
            'data' => [
                'vacancy' => [
                    'id' => $vacancy->id,
                    'title' => $vacancy->title,
                    'position' => $vacancy->position->position_name,
                    'department' => $vacancy->position->department->department_name,
                ],
                'total_candidates' => $results->count(),
                'ranking' => $formatted,
                'note' => 'Final score adalah total gap MAIRCA. Nilai lebih kecil = kandidat lebih unggul.',
            ],
        ]);
    }
}
