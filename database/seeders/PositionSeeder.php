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
            'Human Resources' => [
                'HR Manager',
                'Recruiter',
                'Training & Development Specialist',
            ],
            'Finance & Accounting' => [
                'Financial Analyst',
                'Akuntan',
                'Auditor Internal',
            ],
            'Information Technology' => [
                'Software Engineer',
                'Backend Developer',
                'Frontend Developer',
                'QA Engineer',
                'DevOps Engineer',
            ],
            'Marketing' => [
                'Marketing Manager',
                'Content Creator',
                'SEO Specialist',
                'Brand Strategist',
            ],
            'Operations' => [
                'Operations Manager',
                'Supervisor Produksi',
                'Logistics Coordinator',
            ],
            'Research & Development' => [
                'R&D Analyst',
                'Product Designer',
            ],
            'Customer Service' => [
                'Customer Service Representative',
                'Team Lead CS',
            ],
            'Procurement' => [
                'Procurement Officer',
                'Purchasing Staff',
            ],
        ];

        foreach ($positions as $departmentName => $positionNames) {
            $department = Department::firstOrCreate(
                ['department_name' => $departmentName],
                ['is_active' => true]
            );

            foreach ($positionNames as $positionName) {
                Position::firstOrCreate(
                    [
                        'department_id' => $department->id,
                        'position_name' => $positionName,
                    ],
                    ['is_active' => true]
                );
            }
        }
    }
}
