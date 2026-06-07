<?php

namespace Database\Seeders;

use App\Models\LikertScale;
use App\Models\RecruitmentCriteria;
use Illuminate\Database\Seeder;

class LikertScaleSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua kriteria kualitatif (yang membutuhkan skala Likert)
        $kualitatifCriterias = RecruitmentCriteria::where('data_type', 'kualitatif')->get();

        // Skala Likert 5 titik standar
        $scales = [
            ['label' => 'Sangat Baik',   'value' => 5],
            ['label' => 'Baik',          'value' => 4],
            ['label' => 'Cukup',         'value' => 3],
            ['label' => 'Kurang',        'value' => 2],
            ['label' => 'Sangat Kurang', 'value' => 1],
        ];

        foreach ($kualitatifCriterias as $criteria) {
            foreach ($scales as $scale) {
                LikertScale::firstOrCreate(
                    [
                        'recruitment_criterias_id' => $criteria->id,
                        'label'                    => $scale['label'],
                    ],
                    [
                        'recruitment_criterias_id' => $criteria->id,
                        'label'                    => $scale['label'],
                        'value'                    => $scale['value'],
                    ]
                );
            }
        }
    }
}
