<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );

        // HR
        User::firstOrCreate(
            ['email' => 'hr@example.com'],
            [
                'name'     => 'HR User',
                'password' => Hash::make('password'),
                'role'     => 'hr',
                'status'   => 'active',
            ]
        );

        // Employee
        User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name'     => 'Employee User',
                'password' => Hash::make('password'),
                'role'     => 'employee',
                'status'   => 'active',
            ]
        );
    }
}

