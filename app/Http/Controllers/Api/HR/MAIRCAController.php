<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\RecruitmentResult;
use App\Models\Vacancies;
use App\Services\MAIRCAService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MAIRCAController extends Controller
{
    public function __construct(protected MAIRCAService $maircaService) {}

    /**
     * POST /api/hr/mairca/calculate/{vacancyId}
     * Hitung ranking MAIRCA untuk semua kandidat 'interview_done' di vacancy ini.
     * Hasil disimpan ke recruitment_results.
     */
    public function calculate(string $vacancyId): JsonResponse
    {
        try {
            $result = $this->maircaService->calculate((int) $vacancyId);

            return response()->json([
                'success' => true,
                'message' => 'Kalkulasi MAIRCA berhasil.',
                'data'    => $result,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat kalkulasi MAIRCA.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/hr/mairca/ranking/{vacancyId}
     * Lihat hasil ranking MAIRCA yang sudah terhitung.
     * Menampilkan tabel perbandingan kandidat untuk HR sebagai basis keputusan.
     */
    public function ranking(string $vacancyId): JsonResponse
    {
        $vacancy = Vacancies::with('position.department')->findOrFail($vacancyId);

        $results = RecruitmentResult::with([
            'application.candidate',
            'application.interviewSessions.scores.criteria',
        ])
            ->whereHas('application', fn ($q) => $q->where('vacancy_id', $vacancyId))
            ->orderBy('ranking')
            ->get();

        if ($results->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada hasil kalkulasi MAIRCA untuk vacancy ini. Jalankan kalkulasi terlebih dahulu.',
            ], 404);
        }

        $formatted = $results->map(function ($result) {
            $app       = $result->application;
            $candidate = $app->candidate;

            // Kumpulkan skor per kriteria
            $scores = [];
            foreach ($app->interviewSessions as $session) {
                foreach ($session->scores as $score) {
                    $scores[$score->criteria->name] = $score->score;
                }
            }

            return [
                'ranking'          => $result->ranking,
                'application_id'   => $app->id,
                'application_code' => $app->application_code,
                'candidate_name'   => $candidate->name,
                'candidate_email'  => $candidate->email,
                'final_score'      => $result->final_score,  // total gap (terkecil = terbaik)
                'decission'        => $result->decission,
                'scores'           => $scores,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'vacancy'      => [
                    'id'         => $vacancy->id,
                    'title'      => $vacancy->title,
                    'position'   => $vacancy->position->position_name,
                    'department' => $vacancy->position->department->department_name,
                ],
                'total_candidates' => $results->count(),
                'ranking'          => $formatted,
                'note'             => 'Final score adalah total gap MAIRCA. Nilai lebih kecil = kandidat lebih unggul.',
            ],
        ]);
    }
}
