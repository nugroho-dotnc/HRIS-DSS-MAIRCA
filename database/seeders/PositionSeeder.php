<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $ti  = Department::where('department_name', 'Teknologi Informasi')->first()->id;
        $hr  = Department::where('department_name', 'Human Resources')->first()->id;
        $fin = Department::where('department_name', 'Keuangan')->first()->id;
        $ops = Department::where('department_name', 'Operasional')->first()->id;

        $positions = [
            // Teknologi Informasi
            ['department_id' => $ti,  'position_name' => 'Backend Developer',   'is_active' => true],
            ['department_id' => $ti,  'position_name' => 'Frontend Developer',  'is_active' => true],
            ['department_id' => $ti,  'position_name' => 'DevOps Engineer',     'is_active' => true],
            // Human Resources
            ['department_id' => $hr,  'position_name' => 'HR Specialist',       'is_active' => true],
            ['department_id' => $hr,  'position_name' => 'Recruitment Officer',  'is_active' => true],
            // Keuangan
            ['department_id' => $fin, 'position_name' => 'Akuntan',             'is_active' => true],
            ['department_id' => $fin, 'position_name' => 'Financial Analyst',   'is_active' => true],
            // Operasional
            ['department_id' => $ops, 'position_name' => 'Operations Manager',  'is_active' => true],
            ['department_id' => $ops, 'position_name' => 'Logistics Staff',     'is_active' => true],
        ];

        foreach ($positions as $pos) {
            Position::firstOrCreate(
                ['department_id' => $pos['department_id'], 'position_name' => $pos['position_name']],
                $pos
            );
        }
    }
}
