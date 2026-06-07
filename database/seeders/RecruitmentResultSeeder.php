<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\RecruitmentResult;
use Illuminate\Database\Seeder;

class RecruitmentResultSeeder extends Seeder
{
    public function run(): void
    {
        // Hasil DSS MAIRCA hanya untuk aplikasi yang sudah final: hired / rejected
        // Kedua aplikasi ini berada di lowongan yang sama (Backend Developer – Laravel)

        // APP-2026-00001: Ahmad → hired → ranking 1, skor lebih tinggi
        $appHired = Application::where('application_code', 'APP-2026-00001')->first();
        RecruitmentResult::firstOrCreate(
            ['application_id' => $appHired->id],
            [
                'application_id' => $appHired->id,
                'final_score'    => 0.8234,  // Skor MAIRCA (0-1, lebih tinggi = lebih baik)
                'ranking'        => 1,
                'decission'      => 'hired',
            ]
        );

        // APP-2026-00002: Rizky → rejected → ranking 2, skor lebih rendah
        $appRejected = Application::where('application_code', 'APP-2026-00002')->first();
        RecruitmentResult::firstOrCreate(
            ['application_id' => $appRejected->id],
            [
                'application_id' => $appRejected->id,
                'final_score'    => 0.4517,  // Skor MAIRCA lebih rendah
                'ranking'        => 2,
                'decission'      => 'rejected',
            ]
        );
    }
}
