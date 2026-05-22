<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hrId = DB::table('users')->where('role', 'hr')->value('id');

        $positions = DB::table('positions')
            ->whereIn('position_name', [
                'Software Engineer',
                'Backend Developer',
                'Frontend Developer',
                'Recruiter',
                'Financial Analyst',
                'Marketing Manager',
                'Customer Service Representative',
                'Procurement Officer',
                'Operations Manager',
                'R&D Analyst',
            ])
            ->pluck('id', 'position_name');

        if (! $hrId || $positions->isEmpty()) {
            $this->command->warn('HR user or positions not found. Please run UserSeeder and PositionSeeder first.');
            return;
        }

        $vacancies = [
            [
                'position_name' => 'Software Engineer',
                'title'         => 'Lowongan Software Engineer – IT Division',
                'description'   => 'Kami membuka kesempatan berkarir sebagai Software Engineer untuk bergabung dalam tim IT kami yang dinamis.',
                'requirements'  => "- Minimal S1 Teknik Informatika atau jurusan terkait\n- Pengalaman minimal 2 tahun\n- Menguasai PHP/Laravel atau Python/Django\n- Familiar dengan Git dan agile workflow",
                'deadline'      => '2026-07-31',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Backend Developer',
                'title'         => 'Lowongan Backend Developer – Divisi IT',
                'description'   => 'Bergabunglah sebagai Backend Developer dan bantu kami membangun sistem backend yang skalabel dan handal.',
                'requirements'  => "- S1 Ilmu Komputer / Teknik Informatika\n- Pengalaman minimal 1 tahun di backend development\n- Menguasai Node.js / Laravel\n- Familiar dengan REST API dan database relasional",
                'deadline'      => '2026-07-15',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Frontend Developer',
                'title'         => 'Lowongan Frontend Developer – Divisi IT',
                'description'   => 'Kami mencari Frontend Developer berbakat untuk menciptakan antarmuka pengguna yang menarik dan responsif.',
                'requirements'  => "- S1 Teknik Informatika atau desain UI/UX\n- Menguasai React.js / Vue.js\n- Pemahaman CSS, HTML5, dan desain responsif\n- Portofolio proyek menjadi nilai tambah",
                'deadline'      => '2026-08-01',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Recruiter',
                'title'         => 'Lowongan Recruiter – Divisi Human Resources',
                'description'   => 'Kami membuka posisi Recruiter untuk membantu proses rekrutmen dan seleksi talenta terbaik bagi perusahaan.',
                'requirements'  => "- S1 Psikologi / Manajemen SDM\n- Pengalaman minimal 1 tahun di posisi rekrutmen\n- Familiar dengan ATS dan job portals\n- Komunikatif dan teliti",
                'deadline'      => '2026-06-30',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Financial Analyst',
                'title'         => 'Lowongan Financial Analyst – Divisi Finance',
                'description'   => 'Posisi Financial Analyst dibutuhkan untuk mendukung analisis keuangan dan perencanaan anggaran perusahaan.',
                'requirements'  => "- S1 Akuntansi / Keuangan\n- Pengalaman minimal 2 tahun di bidang analisis keuangan\n- Menguasai Excel tingkat lanjut dan perangkat BI\n- Memiliki sertifikasi CFA menjadi nilai tambah",
                'deadline'      => '2026-06-15',
                'status'        => 'closed',
            ],
            [
                'position_name' => 'Marketing Manager',
                'title'         => 'Lowongan Marketing Manager – Divisi Marketing',
                'description'   => 'Kami mencari Marketing Manager berpengalaman untuk memimpin strategi pemasaran digital dan konvensional.',
                'requirements'  => "- S1 Pemasaran / Komunikasi / Bisnis\n- Pengalaman minimal 4 tahun di bidang marketing\n- Pernah memimpin tim\n- Memiliki pemahaman kuat tentang digital marketing dan analitik",
                'deadline'      => '2026-09-01',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Customer Service Representative',
                'title'         => 'Lowongan Customer Service Representative',
                'description'   => 'Posisi CS Representative untuk melayani dan menangani pertanyaan serta keluhan pelanggan secara profesional.',
                'requirements'  => "- D3/S1 semua jurusan\n- Pengalaman di bidang customer service diutamakan\n- Komunikatif, sabar, dan ramah\n- Mampu mengoperasikan komputer dan CRM tools",
                'deadline'      => '2026-07-01',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Procurement Officer',
                'title'         => 'Lowongan Procurement Officer – Divisi Pengadaan',
                'description'   => 'Kami membutuhkan Procurement Officer untuk mengelola pengadaan barang dan jasa perusahaan secara efisien.',
                'requirements'  => "- S1 Manajemen / Teknik Industri\n- Pengalaman minimal 2 tahun di bidang pengadaan\n- Memahami proses tender dan negosiasi vendor\n- Teliti dan mampu bekerja dengan tenggat waktu ketat",
                'deadline'      => '2026-08-15',
                'status'        => 'open',
            ],
            [
                'position_name' => 'Operations Manager',
                'title'         => 'Lowongan Operations Manager – Divisi Operasional',
                'description'   => 'Operations Manager dibutuhkan untuk mengawasi dan meningkatkan efisiensi proses operasional harian perusahaan.',
                'requirements'  => "- S1 Teknik Industri / Manajemen Operasional\n- Pengalaman minimal 5 tahun, 2 tahun di posisi manajerial\n- Kemampuan analitis dan leadership yang kuat\n- Menguasai tools manajemen proyek",
                'deadline'      => '2026-09-30',
                'status'        => 'open',
            ],
            [
                'position_name' => 'R&D Analyst',
                'title'         => 'Lowongan R&D Analyst – Divisi Riset & Pengembangan',
                'description'   => 'R&D Analyst dibutuhkan untuk melakukan penelitian dan pengembangan produk/layanan perusahaan.',
                'requirements'  => "- S1/S2 bidang Sains, Teknik, atau terkait\n- Pengalaman riset atau magang di industri diutamakan\n- Mampu menyusun laporan penelitian\n- Kemampuan analitis dan kritis yang tinggi",
                'deadline'      => '2026-10-01',
                'status'        => 'open',
            ],
        ];

        foreach ($vacancies as $vacancy) {
            $positionId = $positions[$vacancy['position_name']] ?? null;
            if (! $positionId) continue;

            $exists = DB::table('vacancies')
                ->where('title', $vacancy['title'])
                ->exists();

            if (! $exists) {
                DB::table('vacancies')->insert([
                    'hr_id'        => $hrId,
                    'position_id'  => $positionId,
                    'title'        => $vacancy['title'],
                    'description'  => $vacancy['description'],
                    'requirements' => $vacancy['requirements'],
                    'deadline'     => $vacancy['deadline'],
                    'status'       => $vacancy['status'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
