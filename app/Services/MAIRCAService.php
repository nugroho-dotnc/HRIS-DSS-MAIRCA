<?php

namespace App\Services;

use App\Models\Application;
use App\Models\RecruitmentCriteria;
use App\Models\RecruitmentResult;
use App\Models\Vacancies;
use Illuminate\Support\Collection;

/**
 * MAIRCAService — Engine Kalkulasi DSS MAIRCA
 *
 * Metode MAIRCA (Multi-Attributive Ideal-Real Comparative Analysis)
 * digunakan untuk meranking kandidat berdasarkan gap antara nilai ideal
 * dan nilai riil per kriteria yang sudah dibobot.
 *
 * Langkah:
 *  1. Hitung preferensi pemilihan (Pi) — proporsional (uniform distribution)
 *  2. Hitung matrix teoritis (Tp) — bobot × Pi
 *  3. Hitung matrix riil (Tr) — normalisasi skor per kriteria
 *  4. Hitung gap (G) = Tp - Tr
 *  5. Hitung total gap per kandidat (Q)
 *  6. Ranking berdasarkan Q (terkecil = terbaik)
 */
class MAIRCAService
{
    /**
     * Kalkulasi ranking MAIRCA untuk semua kandidat 'interview_done'
     * pada vacancy tertentu.
     *
     * @param  int  $vacancyId
     * @return array  Hasil ranking + detail kalkulasi
     */
    public function calculate(int $vacancyId): array
    {
        $vacancy = Vacancies::with(['position.recruitment_criteria.likertScales'])->findOrFail($vacancyId);

        // Ambil semua aplikasi dengan status interview_done
        $applications = Application::where('vacancy_id', $vacancyId)
            ->where('status', 'interview_done')
            ->with([
                'candidate',
                'interviewSessions.scores.criteria',
            ])
            ->get();

        if ($applications->isEmpty()) {
            throw new \RuntimeException('Tidak ada kandidat dengan status interview_done untuk vacancy ini.');
        }

        // Ambil kriteria MAIRCA untuk posisi ini
        $criteria = $vacancy->position->recruitment_criteria;

        if ($criteria->isEmpty()) {
            throw new \RuntimeException('Belum ada kriteria MAIRCA yang dikonfigurasi untuk posisi ini.');
        }

        // Validasi total bobot = 100%
        $totalWeight = $criteria->sum('weight');
        if (abs($totalWeight - 100) > 0.01) {
            throw new \RuntimeException("Total bobot kriteria harus 100%. Saat ini: {$totalWeight}%.");
        }

        $numAlternatives = $applications->count();

        // ─── Step 1: Preferensi Pemilihan (Pi) — uniform distribution ──────────
        // Pi = 1 / jumlah_alternatif (semua kandidat dianggap setara sebelum dievaluasi)
        $Pi = 1 / $numAlternatives;

        // ─── Step 2: Matrix Teoritis (Tp) ───────────────────────────────────────
        // Tp[i][j] = bobot[j] / 100 × Pi
        $Tp = [];
        foreach ($criteria as $c) {
            $Tp[$c->id] = ($c->weight / 100) * $Pi;
        }

        // ─── Step 3: Kumpulkan skor per kandidat per kriteria ───────────────────
        $rawScores = []; // rawScores[application_id][criteria_id] = score

        foreach ($applications as $app) {
            $rawScores[$app->id] = [];
            foreach ($app->interviewSessions as $session) {
                foreach ($session->scores as $score) {
                    $rawScores[$app->id][$score->criteria_id] = (float) $score->score;
                }
            }
        }

        // ─── Step 4: Normalisasi Skor → Matrix Riil (Tr) ───────────────────────
        // Untuk setiap kriteria, normalisasi nilai kandidat ke rentang [0, 1]
        // Benefit: (x - min) / (max - min)
        // Cost:    (max - x) / (max - min)
        $normalizedScores = [];

        foreach ($criteria as $c) {
            $cid    = $c->id;
            $values = collect($applications)->map(fn ($a) => $rawScores[$a->id][$cid] ?? 0)->toArray();
            $max    = max($values);
            $min    = min($values);
            $range  = $max - $min;

            foreach ($applications as $app) {
                $raw = $rawScores[$app->id][$cid] ?? 0;

                if ($range == 0) {
                    $normalized = 1.0; // Semua nilai sama → normalized = 1
                } elseif ($c->type === 'benefit') {
                    $normalized = ($raw - $min) / $range;
                } else {
                    // cost: semakin kecil semakin baik
                    $normalized = ($max - $raw) / $range;
                }

                $normalizedScores[$app->id][$cid] = $normalized;
            }
        }

        // ─── Step 5: Hitung Gap (G) dan Total Gap (Q) ───────────────────────────
        // Tr[i][j] = Tp[j] × normalized[i][j]
        // G[i][j]  = Tp[j] - Tr[i][j]
        // Q[i]     = sum(G[i][j]) untuk semua j

        $results = [];

        foreach ($applications as $app) {
            $totalGap   = 0;
            $gapDetails = [];

            foreach ($criteria as $c) {
                $cid    = $c->id;
                $tp     = $Tp[$cid];
                $tr     = $tp * ($normalizedScores[$app->id][$cid] ?? 0);
                $gap    = $tp - $tr;

                $totalGap += $gap;

                $gapDetails[] = [
                    'criteria_id'   => $cid,
                    'criteria_name' => $c->name,
                    'weight'        => $c->weight,
                    'type'          => $c->type,
                    'raw_score'     => $rawScores[$app->id][$cid] ?? 0,
                    'normalized'    => round($normalizedScores[$app->id][$cid] ?? 0, 6),
                    'Tp'            => round($tp, 6),
                    'Tr'            => round($tr, 6),
                    'gap'           => round($gap, 6),
                ];
            }

            $results[$app->id] = [
                'application_id'  => $app->id,
                'application_code'=> $app->application_code,
                'candidate'       => $app->candidate,
                'total_gap'       => round($totalGap, 6),
                'gap_details'     => $gapDetails,
            ];
        }

        // ─── Step 6: Ranking (ascending by total_gap — gap terkecil = terbaik) ──
        usort($results, fn ($a, $b) => $a['total_gap'] <=> $b['total_gap']);

        $ranking = 1;
        foreach ($results as &$r) {
            $r['ranking'] = $ranking++;
        }

        // ─── Step 7: Simpan ke recruitment_results ───────────────────────────────
        foreach ($results as $r) {
            RecruitmentResult::updateOrCreate(
                ['application_id' => $r['application_id']],
                [
                    'final_score' => $r['total_gap'],
                    'ranking'     => $r['ranking'],
                    'decission'   => 'rejected', // Default rejected, HR yang override ke hired
                ]
            );
        }

        return [
            'vacancy_id'       => $vacancyId,
            'vacancy_title'    => $vacancy->title,
            'position'         => $vacancy->position->position_name,
            'num_alternatives' => $numAlternatives,
            'num_criteria'     => $criteria->count(),
            'Pi'               => round($Pi, 6),
            'Tp_matrix'        => collect($criteria)->mapWithKeys(fn ($c) => [
                $c->name => round($Tp[$c->id], 6),
            ]),
            'ranking'          => $results,
            'calculated_at'    => now()->toDateTimeString(),
        ];
    }
}
