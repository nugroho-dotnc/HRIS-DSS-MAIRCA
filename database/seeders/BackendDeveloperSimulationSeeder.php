<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class BackendDeveloperSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Get or Create HR User
        $hrUser = User::where('role', 'hr')->first();
        if (!$hrUser) {
            $hrUserId = DB::table('users')->insertGetId([
                'name' => 'HR Manager',
                'email' => 'hr@example.com',
                'password' => bcrypt('password'),
                'role' => 'hr',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $hrUser = User::find($hrUserId);
        }

        // 2. Get or Create Department
        $departmentId = DB::table('departments')->insertGetId([
            'department_name' => 'Software Development',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Create Position
        $positionId = DB::table('positions')->insertGetId([
            'department_id' => $departmentId,
            'position_name' => 'Backend Developer',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 4. Create Criteria
        $criteriaData = [
            ['name' => 'Kemampuan membuat dan merancang aplikasi', 'type' => 'benefit', 'data_type' => 'kualitatif', 'weight' => 0.15],
            ['name' => 'Database dan instalasi', 'type' => 'benefit', 'data_type' => 'kualitatif', 'weight' => 0.15],
            ['name' => 'Penguasaan API', 'type' => 'benefit', 'data_type' => 'kualitatif', 'weight' => 0.15],
            ['name' => 'Penguasaan Versioning Control', 'type' => 'benefit', 'data_type' => 'kualitatif', 'weight' => 0.10],
            ['name' => 'Penguasaan JSON & Javascript', 'type' => 'benefit', 'data_type' => 'kualitatif', 'weight' => 0.10],
            ['name' => 'Pengalaman Kerja', 'type' => 'benefit', 'data_type' => 'kuantitatif', 'weight' => 0.15],
            ['name' => 'Jarak Rumah', 'type' => 'cost', 'data_type' => 'kuantitatif', 'weight' => 0.10],
            ['name' => 'usia', 'type' => 'cost', 'data_type' => 'kuantitatif', 'weight' => 0.10]
        ];

        $criteriaIds = [];
        foreach ($criteriaData as $c) {
            $cId = DB::table('recruitment_criterias')->insertGetId([
                'position_id' => $positionId,
                'name' => $c['name'],
                'weight' => $c['weight'],
                'description' => $c['name'],
                'type' => $c['type'],
                'data_type' => $c['data_type'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $criteriaIds[] = $cId;

            if ($c['data_type'] == 'kualitatif') {
                // Likert Scales 1-5
                $likertScales = [
                    ['label' => 'Sangat Kurang', 'value' => 1],
                    ['label' => 'Kurang', 'value' => 2],
                    ['label' => 'Cukup', 'value' => 3],
                    ['label' => 'Baik', 'value' => 4],
                    ['label' => 'Sangat Baik', 'value' => 5],
                ];

                foreach ($likertScales as $scale) {
                    DB::table('likert_scales')->insert([
                        'recruitment_criterias_id' => $cId,
                        'label' => $scale['label'],
                        'value' => $scale['value'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // 5. Create Vacancy
        $vacancyId = DB::table('vacancies')->insertGetId([
            'hr_id' => $hrUser->id,
            'position_id' => $positionId,
            'title' => 'Backend Developer',
            'description' => 'Mencari Backend Developer berpengalaman.',
            'requirements' => 'Menguasai Laravel, MySQL, REST API',
            'deadline' => $now->copy()->addDays(30),
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 6. Create Candidates and their Applications
        $candidates = [
            ["name" => "Ahmad", "scores" => [5, 4, 4, 3, 5, 3, 15, 26]],
            ["name" => "Budi", "scores" => [4, 5, 3, 4, 4, 5, 10, 28]],
            ["name" => "Citra", "scores" => [3, 4, 5, 5, 4, 2, 5, 23]],
            ["name" => "Dimas", "scores" => [4, 4, 4, 4, 4, 4, 25, 27]],
            ["name" => "Eka", "scores" => [5, 3, 4, 3, 5, 1, 30, 22]],
        ];

        $appCodePrefix = "APP-BE-";
        $codeIndex = 1;

        foreach ($candidates as $candidate) {
            $candidateId = DB::table('candidates')->insertGetId([
                'name' => $candidate['name'],
                'email' => strtolower($candidate['name']) . '@example.com',
                'phone' => '0812345678' . $codeIndex,
                'gender' => ($candidate['name'] == 'Citra' || $candidate['name'] == 'Eka') ? 'P' : 'L',
                'city' => 'Jakarta',
                'zip_code' => '10000',
                'complete_address' => 'Jl. Dummy ' . $codeIndex,
                'experience' => 'Pengalaman ' . $candidate['scores'][5] . ' tahun',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create Application
            $applicationId = DB::table('applications')->insertGetId([
                'candidate_id' => $candidateId,
                'vacancy_id' => $vacancyId,
                'status' => 'interview_done',
                'application_code' => $appCodePrefix . str_pad($codeIndex, 3, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create Interview Session
            $sessionId = DB::table('interview_sessions')->insertGetId([
                'application_id' => $applicationId,
                'interviewer_id' => $hrUser->id,
                'interview_date' => $now->copy()->subDays(1),
                'notes' => 'Interview dengan ' . $candidate['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Create Interview Scores
            foreach ($candidate['scores'] as $index => $score) {
                DB::table('interview_scores')->insert([
                    'session_id' => $sessionId,
                    'criteria_id' => $criteriaIds[$index],
                    'score' => $score,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $codeIndex++;
        }
    }
}
