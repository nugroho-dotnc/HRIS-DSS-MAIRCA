<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['department_name' => 'Teknologi Informasi', 'is_active' => true],
            ['department_name' => 'Human Resources',     'is_active' => true],
            ['department_name' => 'Keuangan',            'is_active' => true],
            ['department_name' => 'Operasional',         'is_active' => true],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['department_name' => $dept['department_name']],
                $dept
            );
        }
    }
}
