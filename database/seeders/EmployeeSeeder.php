<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve user IDs
        $emp1User = User::where('email', 'emp1@hris.test')->first()->id;
        $emp2User = User::where('email', 'emp2@hris.test')->first()->id;
        $emp3User = User::where('email', 'emp3@hris.test')->first()->id;

        // Resolve department & position IDs
        $tiDept  = Department::where('department_name', 'Teknologi Informasi')->first()->id;
        $hrDept  = Department::where('department_name', 'Human Resources')->first()->id;

        $devOpsPos      = Position::where('position_name', 'DevOps Engineer')->first()->id;
        $recruitmentPos = Position::where('position_name', 'Recruitment Officer')->first()->id;
        $backendPos     = Position::where('position_name', 'Backend Developer')->first()->id;

        // ── Employee 3: Hendro → supervisor (dibuat pertama agar bisa di-reference) ──
        $supervisor = Employee::firstOrCreate(
            ['user_id' => $emp3User],
            [
                'user_id'         => $emp3User,
                'department_id'   => $tiDept,
                'position_id'     => $devOpsPos,
                'supervisor_id'   => 1, // temporary, akan di-update ke diri sendiri
                'join_date'       => Carbon::now()->subMonths(24)->toDateString(),
                'contract_status' => 'permanent',
            ]
        );

        // Update supervisor_id ke diri sendiri (self-referencing untuk top-level supervisor)
        $supervisor->update(['supervisor_id' => $supervisor->id]);

        // ── Employee 1: Budi → karyawan biasa di TI, di bawah Hendro ──
        Employee::firstOrCreate(
            ['user_id' => $emp1User],
            [
                'user_id'         => $emp1User,
                'department_id'   => $tiDept,
                'position_id'     => $backendPos,
                'supervisor_id'   => $supervisor->id,
                'join_date'       => Carbon::now()->subMonths(12)->toDateString(),
                'contract_status' => 'contract',
            ]
        );

        // ── Employee 2: Rina → karyawan baru di HR, di bawah Hendro (cross-dept supervision) ──
        Employee::firstOrCreate(
            ['user_id' => $emp2User],
            [
                'user_id'         => $emp2User,
                'department_id'   => $hrDept,
                'position_id'     => $recruitmentPos,
                'supervisor_id'   => $supervisor->id,
                'join_date'       => Carbon::now()->subMonths(6)->toDateString(),
                'contract_status' => 'probation',
            ]
        );
    }
}
