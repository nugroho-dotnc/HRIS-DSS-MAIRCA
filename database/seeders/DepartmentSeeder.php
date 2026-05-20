<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['department_name' => 'Human Resources', 'is_active' => true],
            ['department_name' => 'Finance & Accounting', 'is_active' => true],
            ['department_name' => 'Information Technology', 'is_active' => true],
            ['department_name' => 'Marketing', 'is_active' => true],
            ['department_name' => 'Operations', 'is_active' => true],
            ['department_name' => 'Research & Development', 'is_active' => true],
            ['department_name' => 'Customer Service', 'is_active' => true],
            ['department_name' => 'Legal & Compliance', 'is_active' => false],
            ['department_name' => 'Procurement', 'is_active' => true],
            ['department_name' => 'General Affairs', 'is_active' => false],
        ];

        DB::table('departments')->insert(
            array_map(fn($d) => array_merge($d, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), $departments)
        );
    }
}
