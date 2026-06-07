<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\RecruitmentCriteria;
use App\Models\Position;
use Illuminate\Database\Seeder;

class InterviewScoreSeeder extends Seeder
{
    public function run(): void
    {
        // Skor hanya untuk aplikasi: interview_done, hired, rejected
        // (BUKAN interview_scheduled — belum dilaksanakan)

        // ══════════════════════════════════════════════════
        // Lowongan 1: Backend Developer – Kriteria 5 buah
        // ══════════════════════════════════════════════════
        $backendPos  = Position::where('position_name', 'Backend Developer')->first()->id;
        $backendCriteria = RecruitmentCriteria::where('position_id', $backendPos)
            ->orderBy('id')->get();

        // --- APP-2026-00001: Ahmad → hired (skor tinggi) ---
        $app1Session = InterviewSession::whereHas('application', function ($q) {
            $q->where('application_code', 'APP-2026-00001');
        })->first();

        // Skor tinggi → kandidat terpilih
        $ahmadScores = [
            4,     // Kemampuan Teknis (kualitatif, likert 1-5) → Baik
            36,    // Pengalaman Kerja (kuantitatif, bulan) → 36 bulan = 3 tahun
            5,     // Problem Solving (kualitatif, likert 1-5) → Sangat Baik
            4,     // Komunikasi (kualitatif, likert 1-5) → Baik
            8,     // Ekspektasi Gaji (kuantitatif, juta Rp) → 8 juta (cost: lebih rendah lebih baik)
        ];

        foreach ($backendCriteria as $index => $criteria) {
            InterviewScore::firstOrCreate(
                ['session_id' => $app1Session->id, 'criteria_id' => $criteria->id],
                [
                    'session_id'  => $app1Session->id,
                    'criteria_id' => $criteria->id,
                    'score'       => $ahmadScores[$index],
                ]
            );
        }

        // --- APP-2026-00002: Rizky → rejected (skor lebih rendah) ---
        $app2Session = InterviewSession::whereHas('application', function ($q) {
            $q->where('application_code', 'APP-2026-00002');
        })->first();

        // Skor lebih rendah → tidak lolos
        $rizkyScores = [
            3,     // Kemampuan Teknis → Cukup
            6,     // Pengalaman Kerja → 6 bulan (magang)
            3,     // Problem Solving → Cukup
            3,     // Komunikasi → Cukup
            12,    // Ekspektasi Gaji → 12 juta (cost: lebih tinggi = lebih buruk)
        ];

        foreach ($backendCriteria as $index => $criteria) {
            InterviewScore::firstOrCreate(
                ['session_id' => $app2Session->id, 'criteria_id' => $criteria->id],
                [
                    'session_id'  => $app2Session->id,
                    'criteria_id' => $criteria->id,
                    'score'       => $rizkyScores[$index],
                ]
            );
        }

        // ══════════════════════════════════════════════════
        // Lowongan 2: HR Specialist – Kriteria 4 buah
        // ══════════════════════════════════════════════════
        $hrSpecPos = Position::where('position_name', 'HR Specialist')->first()->id;
        $hrCriteria = RecruitmentCriteria::where('position_id', $hrSpecPos)
            ->orderBy('id')->get();

        // --- APP-2026-00003: Siti → interview_done (skor bagus, menunggu keputusan) ---
        $app3Session = InterviewSession::whereHas('application', function ($q) {
            $q->where('application_code', 'APP-2026-00003');
        })->first();

        $sitiScores = [
            5,     // Kemampuan Interpersonal → Sangat Baik
            4,     // Pengetahuan HR → Baik
            24,    // Pengalaman Rekrutmen (kuantitatif, bulan) → 24 bulan = 2 tahun
            4,     // Kemampuan Administrasi → Baik
        ];

        foreach ($hrCriteria as $index => $criteria) {
            InterviewScore::firstOrCreate(
                ['session_id' => $app3Session->id, 'criteria_id' => $criteria->id],
                [
                    'session_id'  => $app3Session->id,
                    'criteria_id' => $criteria->id,
                    'score'       => $sitiScores[$index],
                ]
            );
        }
    }
}
