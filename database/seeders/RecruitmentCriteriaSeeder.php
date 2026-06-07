<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\RecruitmentCriteria;
use Illuminate\Database\Seeder;

class RecruitmentCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $positions = Position::all()->keyBy('position_name');

        $criterias = [
            // ── Backend Developer (total weight = 1.00) ──────
            [
                'position_id' => $positions['Backend Developer']->id,
                'name'        => 'Kemampuan Teknis',
                'weight'      => 0.35,
                'description' => 'Penguasaan bahasa pemrograman, framework, dan tools backend development.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Backend Developer']->id,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.25,
                'description' => 'Lama pengalaman di bidang backend development (dalam tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Backend Developer']->id,
                'name'        => 'Problem Solving',
                'weight'      => 0.20,
                'description' => 'Kemampuan analitis dan pemecahan masalah teknis.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Backend Developer']->id,
                'name'        => 'Komunikasi',
                'weight'      => 0.10,
                'description' => 'Kemampuan berkomunikasi dan berkolaborasi dalam tim.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Backend Developer']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.10,
                'description' => 'Ekspektasi gaji kandidat (semakin rendah semakin baik bagi perusahaan).',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── Frontend Developer (total weight = 1.00) ──────
            [
                'position_id' => $positions['Frontend Developer']->id,
                'name'        => 'Kemampuan Teknis (Frontend)',
                'weight'      => 0.35,
                'description' => 'Penguasaan React, Vue, HTML, CSS, Javascript.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Frontend Developer']->id,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.25,
                'description' => 'Lama pengalaman di bidang frontend (dalam tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Frontend Developer']->id,
                'name'        => 'UI/UX Awareness',
                'weight'      => 0.20,
                'description' => 'Kepekaan terhadap desain antarmuka dan pengalaman pengguna.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Frontend Developer']->id,
                'name'        => 'Problem Solving',
                'weight'      => 0.10,
                'description' => 'Kemampuan memecahkan masalah tampilan antar browser/device.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Frontend Developer']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.10,
                'description' => 'Ekspektasi gaji kandidat.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── DevOps Engineer (total weight = 1.00) ──────
            [
                'position_id' => $positions['DevOps Engineer']->id,
                'name'        => 'Kemampuan Teknis Infrastruktur',
                'weight'      => 0.40,
                'description' => 'Penguasaan Docker, Kubernetes, CI/CD, dan Cloud (AWS/GCP/Azure).',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['DevOps Engineer']->id,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.20,
                'description' => 'Lama pengalaman DevOps/SysAdmin (dalam tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['DevOps Engineer']->id,
                'name'        => 'Troubleshooting',
                'weight'      => 0.20,
                'description' => 'Kemampuan investigasi dan resolusi insiden server.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['DevOps Engineer']->id,
                'name'        => 'Komunikasi Tim',
                'weight'      => 0.10,
                'description' => 'Kolaborasi antara tim Dev dan tim Ops.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['DevOps Engineer']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.10,
                'description' => 'Ekspektasi gaji kandidat.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── HR Specialist (total weight = 1.00) ──────────
            [
                'position_id' => $positions['HR Specialist']->id,
                'name'        => 'Kemampuan Interpersonal',
                'weight'      => 0.30,
                'description' => 'Kemampuan membangun hubungan dan berinteraksi dengan berbagai pihak.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['HR Specialist']->id,
                'name'        => 'Pengetahuan HR',
                'weight'      => 0.30,
                'description' => 'Pemahaman tentang regulasi ketenagakerjaan, BPJS, dan kebijakan HR.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['HR Specialist']->id,
                'name'        => 'Pengalaman Rekrutmen',
                'weight'      => 0.25,
                'description' => 'Lama pengalaman menangani proses rekrutmen (dalam tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['HR Specialist']->id,
                'name'        => 'Kemampuan Administrasi',
                'weight'      => 0.15,
                'description' => 'Keterampilan administrasi, pengelolaan dokumen, dan penggunaan HRIS.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],

            // ── Recruitment Officer (total weight = 1.00) ──────
            [
                'position_id' => $positions['Recruitment Officer']->id,
                'name'        => 'Kemampuan Interviewing',
                'weight'      => 0.35,
                'description' => 'Kemampuan melakukan wawancara berbasis kompetensi (CBI).',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Recruitment Officer']->id,
                'name'        => 'Pengalaman Rekrutmen',
                'weight'      => 0.25,
                'description' => 'Lama pengalaman (tahun) khusus menangani rekrutmen massal atau spesialis.',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Recruitment Officer']->id,
                'name'        => 'Kemampuan Komunikasi',
                'weight'      => 0.25,
                'description' => 'Kemampuan komunikasi verbal dan persuasif.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Recruitment Officer']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.15,
                'description' => 'Ekspektasi gaji bulanan.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── Akuntan (total weight = 1.00) ──────
            [
                'position_id' => $positions['Akuntan']->id,
                'name'        => 'Pemahaman Akuntansi & Pajak',
                'weight'      => 0.40,
                'description' => 'Penguasaan siklus akuntansi, PSAK, dan perpajakan Indonesia.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Akuntan']->id,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.25,
                'description' => 'Pengalaman kerja di bidang akuntansi (tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Akuntan']->id,
                'name'        => 'Ketelitian',
                'weight'      => 0.25,
                'description' => 'Sikap detail-oriented dalam menyusun laporan keuangan.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Akuntan']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.10,
                'description' => 'Ekspektasi gaji bulanan.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── Financial Analyst (total weight = 1.00) ──────
            [
                'position_id' => $positions['Financial Analyst']->id,
                'name'        => 'Kemampuan Analisis Finansial',
                'weight'      => 0.35,
                'description' => 'Mampu menganalisis rasio, tren, dan proyeksi keuangan.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Financial Analyst']->id,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.25,
                'description' => 'Pengalaman sebagai analis keuangan (tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Financial Analyst']->id,
                'name'        => 'Penguasaan Tools Analisis',
                'weight'      => 0.20,
                'description' => 'Penguasaan Excel Advance, PowerBI, atau ERP System.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Financial Analyst']->id,
                'name'        => 'Presentasi',
                'weight'      => 0.10,
                'description' => 'Kemampuan menyajikan data keuangan kepada manajemen.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Financial Analyst']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.10,
                'description' => 'Ekspektasi gaji bulanan.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── Operations Manager (total weight = 1.00) ──────
            [
                'position_id' => $positions['Operations Manager']->id,
                'name'        => 'Kemampuan Leadership',
                'weight'      => 0.30,
                'description' => 'Kemampuan memimpin tim operasional lintas divisi.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Operations Manager']->id,
                'name'        => 'Pengalaman Manajerial',
                'weight'      => 0.30,
                'description' => 'Pengalaman manajerial operasional (tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Operations Manager']->id,
                'name'        => 'Problem Solving & Keputusan',
                'weight'      => 0.20,
                'description' => 'Cepat dan tepat dalam mengambil keputusan operasional.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Operations Manager']->id,
                'name'        => 'Manajemen Waktu',
                'weight'      => 0.10,
                'description' => 'Manajemen waktu dan proyek secara efisien.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Operations Manager']->id,
                'name'        => 'Ekspektasi Gaji',
                'weight'      => 0.10,
                'description' => 'Ekspektasi gaji bulanan.',
                'type'        => 'cost',
                'data_type'   => 'kuantitatif',
            ],

            // ── Logistics Staff (total weight = 1.00) ──────
            [
                'position_id' => $positions['Logistics Staff']->id,
                'name'        => 'Ketepatan Waktu & Disiplin',
                'weight'      => 0.35,
                'description' => 'Tingkat disiplin dan ketepatan waktu pengiriman.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Logistics Staff']->id,
                'name'        => 'Pemahaman Sistem Logistik',
                'weight'      => 0.25,
                'description' => 'Pemahaman mengenai inventory, warehouse, dan distribusi.',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
            [
                'position_id' => $positions['Logistics Staff']->id,
                'name'        => 'Pengalaman Kerja',
                'weight'      => 0.25,
                'description' => 'Pengalaman kerja di bidang logistik (tahun).',
                'type'        => 'benefit',
                'data_type'   => 'kuantitatif',
            ],
            [
                'position_id' => $positions['Logistics Staff']->id,
                'name'        => 'Kemampuan Komunikasi',
                'weight'      => 0.15,
                'description' => 'Koordinasi dengan tim internal dan pihak eksternal (vendor).',
                'type'        => 'benefit',
                'data_type'   => 'kualitatif',
            ],
        ];

        foreach ($criterias as $criteria) {
            RecruitmentCriteria::firstOrCreate(
                [
                    'position_id' => $criteria['position_id'],
                    'name'        => $criteria['name'],
                ],
                $criteria
            );
        }
    }
}
