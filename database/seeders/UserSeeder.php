<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin
            [
                'name'     => 'Administrator',
                'email'    => 'admin@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ],
            // HR
            [
                'name'     => 'Sari Dewi',
                'email'    => 'sari.dewi@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'hr',
            ],
            [
                'name'     => 'Budi Santoso',
                'email'    => 'budi.santoso@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'hr',
            ],
            // Supervisor
            [
                'name'     => 'Rina Agustina',
                'email'    => 'rina.agustina@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'supervisor',
            ],
            [
                'name'     => 'Doni Pratama',
                'email'    => 'doni.pratama@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'supervisor',
            ],
            // Employee
            [
                'name'     => 'Andi Wijaya',
                'email'    => 'andi.wijaya@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'employee',
            ],
            [
                'name'     => 'Lestari Putri',
                'email'    => 'lestari.putri@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'employee',
            ],
            [
                'name'     => 'Fajar Nugroho',
                'email'    => 'fajar.nugroho@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'employee',
            ],
            [
                'name'     => 'Maya Sari',
                'email'    => 'maya.sari@hris.com',
                'password' => Hash::make('password'),
                'role'     => 'employee',
            ],
            // Candidate
            [
                'name'     => 'Rizky Aditya',
                'email'    => 'rizky.aditya@gmail.com',
                'password' => Hash::make('password'),
                'role'     => 'candidate',
            ],
            [
                'name'     => 'Nita Rahayu',
                'email'    => 'nita.rahayu@gmail.com',
                'password' => Hash::make('password'),
                'role'     => 'candidate',
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
