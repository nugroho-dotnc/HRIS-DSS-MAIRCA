<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Admin ────────────────────────────────────────
            [
                'name'      => 'Super Admin',
                'email'     => 'admin@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'admin',
                'is_active' => true,
            ],

            // ── HR ───────────────────────────────────────────
            [
                'name'      => 'HR Manager',
                'email'     => 'hr1@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'hr',
                'is_active' => true,
            ],
            [
                'name'      => 'HR Staff',
                'email'     => 'hr2@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'hr',
                'is_active' => true,
            ],

            // ── Supervisor ───────────────────────────────────
            [
                'name'      => 'Supervisor Teknik',
                'email'     => 'sup1@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'supervisor',
                'is_active' => true,
            ],
            [
                'name'      => 'Supervisor HRD',
                'email'     => 'sup2@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'supervisor',
                'is_active' => true,
            ],

            // ── Candidate ────────────────────────────────────
            [
                'name'      => 'Ahmad Fauzan',
                'email'     => 'candidate1@gmail.com',
                'password'  => Hash::make('password'),
                'role'      => 'candidate',
                'is_active' => true,
            ],
            [
                'name'      => 'Siti Nurhaliza',
                'email'     => 'candidate2@gmail.com',
                'password'  => Hash::make('password'),
                'role'      => 'candidate',
                'is_active' => true,
            ],
            [
                'name'      => 'Rizky Pratama',
                'email'     => 'candidate3@gmail.com',
                'password'  => Hash::make('password'),
                'role'      => 'candidate',
                'is_active' => true,
            ],
            [
                'name'      => 'Dewi Anggraini',
                'email'     => 'candidate4@gmail.com',
                'password'  => Hash::make('password'),
                'role'      => 'candidate',
                'is_active' => true,
            ],
            [
                'name'      => 'Muhammad Ilham',
                'email'     => 'candidate5@gmail.com',
                'password'  => Hash::make('password'),
                'role'      => 'candidate',
                'is_active' => true,
            ],

            // ── Employee ─────────────────────────────────────
            [
                'name'      => 'Budi Setiawan',
                'email'     => 'emp1@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'employee',
                'is_active' => true,
            ],
            [
                'name'      => 'Rina Marlina',
                'email'     => 'emp2@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'employee',
                'is_active' => true,
            ],
            [
                'name'      => 'Hendro Wibowo',
                'email'     => 'emp3@hris.test',
                'password'  => Hash::make('password'),
                'role'      => 'employee',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
