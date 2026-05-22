<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed employees — setiap employee terhubung ke user, department, position,
     * dan supervisor (self-referencing).
     */
    public function run(): void
    {
        // Ambil user dengan role employee & supervisor
        $supervisorUsers = DB::table('users')->where('role', 'supervisor')->get();
        $employeeUsers   = DB::table('users')->where('role', 'employee')->get();

        $departments = DB::table('departments')->where('is_active', true)->pluck('id', 'department_name');
        $positions   = DB::table('positions')->pluck('id', 'position_name');

        if ($supervisorUsers->isEmpty() || $departments->isEmpty() || $positions->isEmpty()) {
            $this->command->warn('Missing users/departments/positions. Run related seeders first.');
            return;
        }

        // ─── Step 1: Insert supervisor users sebagai employee lebih dulu ───────
        // Karena supervisor_id self-referencing, kita perlu:
        //   a. Insert semua supervisor dengan FK check OFF (pakai ID diri sendiri sebagai placeholder)
        //   b. Lalu update supervisor_id ke nilai valid setelah semua terinput

        $supervisorEmployeeIds = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($supervisorUsers as $supUser) {
            $existing = DB::table('employees')->where('user_id', $supUser->id)->first();
            if ($existing) {
                $supervisorEmployeeIds[$supUser->id] = $existing->id;
                continue;
            }

            $deptId = $departments['Human Resources'] ?? $departments->values()->first();
            $posId  = $positions['HR Manager']        ?? $positions->values()->first();

            $empId = DB::table('employees')->insertGetId([
                'user_id'         => $supUser->id,
                'department_id'   => $deptId,
                'position_id'     => $posId,
                'supervisor_id'   => 1, // temporary placeholder (diupdate di bawah)
                'join_date'       => '2022-01-15',
                'contract_status' => 'permanent',
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $supervisorEmployeeIds[$supUser->id] = $empId;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Update supervisor_id agar valid: supervisor pertama -> supervisor kedua, dst. (circular)
        $allSupervisorEmpIds = array_values($supervisorEmployeeIds);
        foreach ($allSupervisorEmpIds as $i => $empId) {
            $supervisorEmpId = $allSupervisorEmpIds[($i + 1) % count($allSupervisorEmpIds)];
            DB::table('employees')->where('id', $empId)->update(['supervisor_id' => $supervisorEmpId]);
        }

        // ─── Step 2: Insert employee users ────────────────────────────────────
        $defaultSupervisorEmpId = $allSupervisorEmpIds[0];

        $employeeData = [
            [
                'name'            => 'Andi Wijaya',
                'department_name' => 'Information Technology',
                'position_name'   => 'Software Engineer',
                'join_date'       => '2023-03-01',
                'contract_status' => 'permanent',
            ],
            [
                'name'            => 'Lestari Putri',
                'department_name' => 'Marketing',
                'position_name'   => 'Content Creator',
                'join_date'       => '2024-01-10',
                'contract_status' => 'contract',
            ],
            [
                'name'            => 'Fajar Nugroho',
                'department_name' => 'Finance & Accounting',
                'position_name'   => 'Akuntan',
                'join_date'       => '2023-07-20',
                'contract_status' => 'permanent',
            ],
            [
                'name'            => 'Maya Sari',
                'department_name' => 'Customer Service',
                'position_name'   => 'Customer Service Representative',
                'join_date'       => '2024-04-05',
                'contract_status' => 'probation',
            ],
        ];

        foreach ($employeeUsers as $empUser) {
            $exists = DB::table('employees')->where('user_id', $empUser->id)->exists();
            if ($exists) continue;

            // Cari data konfigurasi berdasarkan nama user
            $config = collect($employeeData)->firstWhere('name', $empUser->name);

            $deptId   = $config ? ($departments[$config['department_name']] ?? $departments->values()->first()) : $departments->values()->first();
            $posId    = $config ? ($positions[$config['position_name']]    ?? $positions->values()->first())   : $positions->values()->first();
            $joinDate = $config['join_date']       ?? '2024-01-01';
            $contract = $config['contract_status'] ?? 'permanent';

            DB::table('employees')->insert([
                'user_id'         => $empUser->id,
                'department_id'   => $deptId,
                'position_id'     => $posId,
                'supervisor_id'   => $defaultSupervisorEmpId,
                'join_date'       => $joinDate,
                'contract_status' => $contract,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }
}
