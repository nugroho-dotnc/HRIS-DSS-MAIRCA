<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            RecruitmentCriteriaSeeder::class,
            LikertScaleSeeder::class,
            VacancySeeder::class,
            CandidateSeeder::class,
            ApplicationSeeder::class,
            InterviewSessionSeeder::class,
            InterviewScoreSeeder::class,
            RecruitmentResultSeeder::class,
            EmployeeSeeder::class,
            NotificationSeeder::class,
            FcmTokenSeeder::class,
        ]);
    }
}
