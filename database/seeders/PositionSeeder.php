<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'Teknik Informatika' => ['Software Engineer', 'Backend Developer', 'Frontend Developer', 'QA Engineer'],
            'Keuangan' => ['Financial Analyst', 'Akuntan', 'Auditor'],
            'Sumber Daya Manusia' => ['HR Manager', 'Recruiter', 'Training Specialist'],
            'Pemasaran' => ['Marketing Manager', 'Content Creator', 'SEO Specialist'],
        ];

        foreach ($positions as $departmentName => $positionNames) {
            $department = Department::firstOrCreate([
                'department_name' => $departmentName,
            ], [
                'is_active' => true,
            ]);

            foreach ($positionNames as $positionName) {
                Position::create([
                    'department_id' => $department->id,
                    'position_name' => $positionName,
                    'is_active' => true,
                ]);
            }
        }
    }
}
