<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecruitmentResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed recruitment_results untuk aplikasi yang sudah interview_done atau hired.
     */
    public function run(): void
    {
        $applications = DB::table('applications')
            ->whereIn('status', ['interview_done', 'hired'])
            ->select('id', 'status')
            ->get();

        if ($applications->isEmpty()) {
            $this->command->warn('No interview-done/hired applications found. Run ApplicationSeeder first.');
            return;
        }

        // Data skor MAIRCA final per aplikasi (simulasi hasil perhitungan)
        $resultData = [
            ['final_score' => 0.8523, 'decission' => 'hired'],
            ['final_score' => 0.7914, 'decission' => 'hired'],
            ['final_score' => 0.7235, 'decission' => 'hired'],
            ['final_score' => 0.6811, 'decission' => 'rejected'],
            ['final_score' => 0.6102, 'decission' => 'rejected'],
        ];

        foreach ($applications as $i => $application) {
            $exists = DB::table('recruitment_results')
                ->where('application_id', $application->id)
                ->exists();

            if (! $exists) {
                $data = $resultData[$i % count($resultData)];

                // Jika status aplikasi 'hired', keputusan pasti hired
                $decission = ($application->status === 'hired') ? 'hired' : $data['decission'];

                DB::table('recruitment_results')->insert([
                    'application_id' => $application->id,
                    'final_score'    => $data['final_score'],
                    'ranking'        => $i + 1,
                    'decission'      => $decission,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }
}
