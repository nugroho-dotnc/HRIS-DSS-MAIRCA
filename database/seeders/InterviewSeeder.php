<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed interview_sessions dan interview_scores untuk aplikasi dengan
     * status interview_done atau hired.
     */
    public function run(): void
    {
        // Ambil user supervisor sebagai interviewer
        $interviewers = DB::table('users')
            ->whereIn('role', ['supervisor', 'hr'])
            ->pluck('id')
            ->toArray();

        if (empty($interviewers)) {
            $this->command->warn('No supervisor/hr users found. Run UserSeeder first.');
            return;
        }

        // Aplikasi yang sudah melewati tahap interview
        $applications = DB::table('applications')
            ->whereIn('status', ['interview_done', 'hired'])
            ->pluck('id')
            ->toArray();

        if (empty($applications)) {
            $this->command->warn('No interview-stage applications found. Run ApplicationSeeder first.');
            return;
        }

        foreach ($applications as $index => $applicationId) {
            // Cek apakah sesi sudah ada
            $exists = DB::table('interview_sessions')
                ->where('application_id', $applicationId)
                ->exists();

            if ($exists) continue;

            $interviewerId = $interviewers[$index % count($interviewers)];

            // Insert interview session
            $sessionId = DB::table('interview_sessions')->insertGetId([
                'application_id' => $applicationId,
                'interviewer_id' => $interviewerId,
                'interview_date' => now()->subDays(rand(3, 20))->format('Y-m-d H:i:s'),
                'notes'          => $this->generateNotes($index),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Ambil kriteria terkait vacancy dari aplikasi ini
            $vacancyId  = DB::table('applications')->where('id', $applicationId)->value('vacancy_id');
            $positionId = DB::table('vacancies')->where('id', $vacancyId)->value('position_id');

            $criteriaIds = DB::table('recruitment_criterias')
                ->where('position_id', $positionId)
                ->pluck('id')
                ->toArray();

            if (empty($criteriaIds)) {
                // Gunakan semua kriteria yang ada jika tidak ada yang spesifik
                $criteriaIds = DB::table('recruitment_criterias')->pluck('id')->toArray();
            }

            // Insert interview scores untuk setiap kriteria
            $sampleScores = [3.5, 4.0, 4.5, 3.0, 5.0, 4.2, 3.8, 4.7];
            foreach ($criteriaIds as $i => $criteriaId) {
                $existingScore = DB::table('interview_scores')
                    ->where('session_id', $sessionId)
                    ->where('criteria_id', $criteriaId)
                    ->exists();

                if (! $existingScore) {
                    DB::table('interview_scores')->insert([
                        'session_id'  => $sessionId,
                        'criteria_id' => $criteriaId,
                        'score'       => $sampleScores[$i % count($sampleScores)],
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
    }

    private function generateNotes(int $index): string
    {
        $notes = [
            'Kandidat menunjukkan pemahaman teknis yang baik. Komunikasi lancar, mampu menjelaskan pengalaman kerja dengan detail.',
            'Kandidat cukup kompeten secara teknis namun perlu pengembangan di sisi soft skill dan manajemen waktu.',
            'Wawancara berjalan baik. Kandidat sangat antusias dan menunjukkan potensi besar untuk berkembang.',
            'Pengalaman kandidat sangat relevan dengan kebutuhan posisi. Direkomendasikan untuk lanjut ke tahap berikutnya.',
            'Kandidat memiliki latar belakang akademik kuat. Pengalaman praktis masih terbatas namun attitude positif.',
        ];

        return $notes[$index % count($notes)];
    }
}
