<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Urutan seeder penting karena ada relasi foreign key antar tabel.
     */
    public function run(): void
    {
        $this->call([
            // 1. Users (tidak ada dependency)
            UserSeeder::class,

            // 2. Departments (tidak ada dependency)
            DepartmentSeeder::class,

            // 3. Positions (butuh departments)
            PositionSeeder::class,

            // 4. Recruitment Criterias + Likert Scales (butuh positions)
            CriteriaSeeder::class,

            // 5. Vacancies (butuh users HR + positions)
            VacancySeeder::class,

            // 6. Candidates (tidak ada dependency)
            CandidateSeeder::class,

            // 7. Applications (butuh candidates + vacancies)
            ApplicationSeeder::class,

            // 8. Employees (butuh users + departments + positions, self-referencing supervisor)
            EmployeeSeeder::class,

            // 9. Interview Sessions + Scores (butuh applications + users + criterias)
            InterviewSeeder::class,

            // 10. Recruitment Results (butuh applications)
            RecruitmentResultSeeder::class,
        ]);
    }
}
