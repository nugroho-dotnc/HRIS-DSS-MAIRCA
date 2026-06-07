<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancies;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve IDs secara dinamis
        $vacancy1 = Vacancies::where('title', 'Backend Developer – Laravel')->first()->id;
        $vacancy2 = Vacancies::where('title', 'HR Specialist')->first()->id;
        $vacancy3 = Vacancies::where('title', 'Frontend Developer React')->first()->id;

        $candidate1 = Candidate::where('email', 'ahmad.fauzan@gmail.com')->first()->id;
        $candidate2 = Candidate::where('email', 'siti.nurhaliza@gmail.com')->first()->id;
        $candidate3 = Candidate::where('email', 'rizky.pratama@gmail.com')->first()->id;
        $candidate4 = Candidate::where('email', 'dewi.anggraini@gmail.com')->first()->id;
        $candidate5 = Candidate::where('email', 'muhammad.ilham@gmail.com')->first()->id;

        $applications = [
            // ── Lowongan 1 (Backend Dev – closed, sudah selesai proses) ──

            // Ahmad melamar Backend → HIRED (kandidat terpilih)
            [
                'candidate_id'     => $candidate1,
                'vacancy_id'       => $vacancy1,
                'status'           => 'hired',
                'application_code' => 'APP-2026-00001',
                'created_at'       => Carbon::now()->subDays(45),
                'updated_at'       => Carbon::now()->subDays(30),
            ],
            // Rizky melamar Backend → REJECTED (tidak lolos)
            [
                'candidate_id'     => $candidate3,
                'vacancy_id'       => $vacancy1,
                'status'           => 'rejected',
                'application_code' => 'APP-2026-00002',
                'created_at'       => Carbon::now()->subDays(44),
                'updated_at'       => Carbon::now()->subDays(30),
            ],

            // ── Lowongan 2 (HR Specialist – open, sedang proses) ────────

            // Siti melamar HR Spec → INTERVIEW_DONE (sudah selesai wawancara, menunggu DSS)
            [
                'candidate_id'     => $candidate2,
                'vacancy_id'       => $vacancy2,
                'status'           => 'interview_done',
                'application_code' => 'APP-2026-00003',
                'created_at'       => Carbon::now()->subDays(10),
                'updated_at'       => Carbon::now()->subDays(3),
            ],
            // Dewi melamar HR Spec → INTERVIEW_SCHEDULED (akan diwawancarai)
            [
                'candidate_id'     => $candidate4,
                'vacancy_id'       => $vacancy2,
                'status'           => 'interview_scheduled',
                'application_code' => 'APP-2026-00004',
                'created_at'       => Carbon::now()->subDays(8),
                'updated_at'       => Carbon::now()->subDays(2),
            ],
            // Ilham juga melamar HR Spec → SCREENING (masih tahap review CV)
            [
                'candidate_id'     => $candidate5,
                'vacancy_id'       => $vacancy2,
                'status'           => 'screening',
                'application_code' => 'APP-2026-00005',
                'created_at'       => Carbon::now()->subDays(5),
                'updated_at'       => Carbon::now()->subDays(4),
            ],

            // ── Lowongan 3 (Frontend React – open, baru dibuka) ────────

            // Ilham melamar Frontend → APPLIED (baru masuk)
            [
                'candidate_id'     => $candidate5,
                'vacancy_id'       => $vacancy3,
                'status'           => 'applied',
                'application_code' => 'APP-2026-00006',
                'created_at'       => Carbon::now()->subDays(2),
                'updated_at'       => Carbon::now()->subDays(2),
            ],
            // Rizky melamar Frontend → APPLIED (baru masuk)
            [
                'candidate_id'     => $candidate3,
                'vacancy_id'       => $vacancy3,
                'status'           => 'applied',
                'application_code' => 'APP-2026-00007',
                'created_at'       => Carbon::now()->subDays(1),
                'updated_at'       => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($applications as $appData) {
            Application::firstOrCreate(
                ['application_code' => $appData['application_code']],
                $appData
            );
        }
    }
}
