<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CriteriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seed recruitment_criterias + likert_scales untuk posisi Software Engineer (position_id = 3)
     * dan Backend Developer (position_id = 4) sebagai contoh.
     */
    public function run(): void
    {
        // Ambil position_id dari nama posisi
        $softwareEngineerId = DB::table('positions')
            ->where('position_name', 'Software Engineer')->value('id');

        $backendDevId = DB::table('positions')
            ->where('position_name', 'Backend Developer')->value('id');

        if (! $softwareEngineerId || ! $backendDevId) {
            $this->command->warn('Positions not found. Please run PositionSeeder first.');
            return;
        }

        // ─── Kriteria untuk Software Engineer ───────────────────────────────────
        $criterias = [
            // Kuantitatif (tidak butuh likert)
            [
                'position_id' => $softwareEngineerId,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.20,
                'description' => 'Jumlah tahun pengalaman kerja relevan di bidang software development.',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $softwareEngineerId,
                'name'        => 'IPK',
                'weight'      => 0.10,
                'description' => 'Indeks Prestasi Kumulatif saat lulus perguruan tinggi.',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            // Kualitatif (butuh likert)
            [
                'position_id' => $softwareEngineerId,
                'name'        => 'Kemampuan Teknis',
                'weight'      => 0.25,
                'description' => 'Penguasaan bahasa pemrograman, framework, dan tools yang relevan.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $softwareEngineerId,
                'name'        => 'Kemampuan Komunikasi',
                'weight'      => 0.15,
                'description' => 'Kemampuan berkomunikasi secara lisan dan tulisan dengan tim.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $softwareEngineerId,
                'name'        => 'Kemampuan Problem Solving',
                'weight'      => 0.20,
                'description' => 'Kemampuan menganalisis masalah dan menemukan solusi yang efektif.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $softwareEngineerId,
                'name'        => 'Jarak Tempat Tinggal',
                'weight'      => 0.10,
                'description' => 'Jarak dari tempat tinggal ke kantor dalam kilometer.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ─── Kriteria untuk Backend Developer ───────────────────────────────
            [
                'position_id' => $backendDevId,
                'name'        => 'Pengalaman Backend',
                'weight'      => 0.25,
                'description' => 'Jumlah tahun pengalaman di backend development.',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $backendDevId,
                'name'        => 'Penguasaan Database',
                'weight'      => 0.20,
                'description' => 'Kemampuan merancang dan mengoptimalkan database SQL/NoSQL.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $backendDevId,
                'name'        => 'Pemahaman API & Integrasi',
                'weight'      => 0.20,
                'description' => 'Kemampuan membangun dan mengintegrasikan REST/GraphQL API.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $backendDevId,
                'name'        => 'Kemampuan Teamwork',
                'weight'      => 0.15,
                'description' => 'Kemampuan bekerja secara kolaboratif dalam tim lintas fungsi.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $backendDevId,
                'name'        => 'Gaji yang Diminta',
                'weight'      => 0.20,
                'description' => 'Ekspektasi gaji kandidat dalam satuan jutaan rupiah.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],
        ];

        foreach ($criterias as $criteria) {
            $exists = DB::table('recruitment_criterias')
                ->where('position_id', $criteria['position_id'])
                ->where('name', $criteria['name'])
                ->exists();

            if (! $exists) {
                $id = DB::table('recruitment_criterias')->insertGetId(array_merge($criteria, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                // Jika kualitatif, tambahkan likert scales
                if ($criteria['data_type'] === 'kualitatif') {
                    $likertScales = [
                        ['label' => 'Sangat Kurang', 'value' => 1],
                        ['label' => 'Kurang',        'value' => 2],
                        ['label' => 'Cukup',         'value' => 3],
                        ['label' => 'Baik',          'value' => 4],
                        ['label' => 'Sangat Baik',   'value' => 5],
                    ];

                    foreach ($likertScales as $scale) {
                        DB::table('likert_scales')->insert([
                            'recruitment_criterias_id' => $id,
                            'label'      => $scale['label'],
                            'value'      => $scale['value'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
