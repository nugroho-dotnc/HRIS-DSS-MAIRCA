<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua kandidat dan lowongan yang ada
        $candidates = DB::table('candidates')->pluck('id', 'email');
        $vacancies  = DB::table('vacancies')->pluck('id', 'title');

        if ($candidates->isEmpty() || $vacancies->isEmpty()) {
            $this->command->warn('Candidates or vacancies not found. Run CandidateSeeder and VacancySeeder first.');
            return;
        }

        // Mapping kandidat → lowongan yang dilamar
        $applications = [
            ['candidate_email' => 'ahmad.fauzi@gmail.com',      'vacancy_title' => 'Lowongan Software Engineer – IT Division',              'status' => 'interview_done',      'code' => 'APP-2026-0001'],
            ['candidate_email' => 'bagas.prasetyo@gmail.com',    'vacancy_title' => 'Lowongan Backend Developer – Divisi IT',                'status' => 'interview_done',      'code' => 'APP-2026-0002'],
            ['candidate_email' => 'putri.handayani@gmail.com',   'vacancy_title' => 'Lowongan Frontend Developer – Divisi IT',              'status' => 'interview_scheduled', 'code' => 'APP-2026-0003'],
            ['candidate_email' => 'rizal.maulana@gmail.com',     'vacancy_title' => 'Lowongan Software Engineer – IT Division',              'status' => 'screening',           'code' => 'APP-2026-0004'],
            ['candidate_email' => 'dewi.lestari@gmail.com',      'vacancy_title' => 'Lowongan Recruiter – Divisi Human Resources',          'status' => 'interview_done',      'code' => 'APP-2026-0005'],
            ['candidate_email' => 'hendra.kusuma@gmail.com',     'vacancy_title' => 'Lowongan Financial Analyst – Divisi Finance',          'status' => 'hired',               'code' => 'APP-2026-0006'],
            ['candidate_email' => 'siti.aminah@gmail.com',       'vacancy_title' => 'Lowongan Customer Service Representative',             'status' => 'applied',             'code' => 'APP-2026-0007'],
            ['candidate_email' => 'nurul.fadhilah@gmail.com',    'vacancy_title' => 'Lowongan Marketing Manager – Divisi Marketing',        'status' => 'interview_done',      'code' => 'APP-2026-0008'],
            ['candidate_email' => 'irfan.hakim@gmail.com',       'vacancy_title' => 'Lowongan Procurement Officer – Divisi Pengadaan',      'status' => 'screening',           'code' => 'APP-2026-0009'],
            ['candidate_email' => 'laila.rahmasari@gmail.com',   'vacancy_title' => 'Lowongan R&D Analyst – Divisi Riset & Pengembangan',   'status' => 'applied',             'code' => 'APP-2026-0010'],
        ];

        foreach ($applications as $app) {
            $candidateId = $candidates[$app['candidate_email']] ?? null;
            $vacancyId   = $vacancies[$app['vacancy_title']]   ?? null;

            if (! $candidateId || ! $vacancyId) continue;

            $exists = DB::table('applications')
                ->where('candidate_id', $candidateId)
                ->where('vacancy_id', $vacancyId)
                ->exists();

            if (! $exists) {
                DB::table('applications')->insert([
                    'candidate_id'     => $candidateId,
                    'vacancy_id'       => $vacancyId,
                    'status'           => $app['status'],
                    'application_code' => $app['code'],
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }
}
