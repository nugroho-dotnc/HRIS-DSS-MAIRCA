<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $candidates = [
            [
                'name'             => 'Ahmad Fauzi',
                'email'            => 'ahmad.fauzi@gmail.com',
                'phone'            => '081234567890',
                'gender'           => 'L',
                'city'             => 'Jakarta',
                'zip_code'         => '10110',
                'complete_address' => 'Jl. Sudirman No. 12, Tanah Abang, Jakarta Pusat',
                'experience'       => '3 tahun sebagai Software Developer di PT Teknologi Nusantara. Menguasai PHP, Laravel, dan Vue.js.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => 'https://ahmadfauzi.dev',
            ],
            [
                'name'             => 'Putri Handayani',
                'email'            => 'putri.handayani@gmail.com',
                'phone'            => '082345678901',
                'gender'           => 'P',
                'city'             => 'Bandung',
                'zip_code'         => '40111',
                'complete_address' => 'Jl. Dago No. 45, Coblong, Bandung',
                'experience'       => '2 tahun sebagai Frontend Developer. Menguasai React.js, Tailwind CSS, dan Figma.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => 'https://putridev.com',
            ],
            [
                'name'             => 'Rizal Maulana',
                'email'            => 'rizal.maulana@gmail.com',
                'phone'            => '083456789012',
                'gender'           => 'L',
                'city'             => 'Surabaya',
                'zip_code'         => '60111',
                'complete_address' => 'Jl. Pemuda No. 78, Gubeng, Surabaya',
                'experience'       => 'Fresh graduate S1 Teknik Informatika. Magang 6 bulan di startup sebagai backend developer (Node.js).',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => null,
            ],
            [
                'name'             => 'Dewi Lestari',
                'email'            => 'dewi.lestari@gmail.com',
                'phone'            => '084567890123',
                'gender'           => 'P',
                'city'             => 'Yogyakarta',
                'zip_code'         => '55111',
                'complete_address' => 'Jl. Malioboro No. 10, Gedongtengen, Yogyakarta',
                'experience'       => '4 tahun pengalaman di bidang HR & Rekrutmen. Familiar dengan berbagai platform rekrutmen dan psikotes.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => null,
            ],
            [
                'name'             => 'Hendra Kusuma',
                'email'            => 'hendra.kusuma@gmail.com',
                'phone'            => '085678901234',
                'gender'           => 'L',
                'city'             => 'Semarang',
                'zip_code'         => '50111',
                'complete_address' => 'Jl. Pandanaran No. 22, Semarang Tengah, Semarang',
                'experience'       => '5 tahun sebagai Financial Analyst di perbankan. Menguasai Excel tingkat lanjut, Power BI, dan SAP.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => null,
            ],
            [
                'name'             => 'Siti Aminah',
                'email'            => 'siti.aminah@gmail.com',
                'phone'            => '086789012345',
                'gender'           => 'P',
                'city'             => 'Depok',
                'zip_code'         => '16411',
                'complete_address' => 'Jl. Margonda Raya No. 88, Pancoran Mas, Depok',
                'experience'       => '2 tahun sebagai Customer Service di perusahaan e-commerce. Terbiasa menangani ratusan tiket keluhan per hari.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => null,
            ],
            [
                'name'             => 'Bagas Prasetyo',
                'email'            => 'bagas.prasetyo@gmail.com',
                'phone'            => '087890123456',
                'gender'           => 'L',
                'city'             => 'Bekasi',
                'zip_code'         => '17111',
                'complete_address' => 'Jl. Ahmad Yani No. 55, Bekasi Selatan, Bekasi',
                'experience'       => '3 tahun sebagai Backend Developer spesialisasi Python/Django dan PostgreSQL. Berpengalaman membangun microservices.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => 'https://github.com/bagasp',
            ],
            [
                'name'             => 'Nurul Fadhilah',
                'email'            => 'nurul.fadhilah@gmail.com',
                'phone'            => '088901234567',
                'gender'           => 'P',
                'city'             => 'Tangerang',
                'zip_code'         => '15111',
                'complete_address' => 'Jl. MH Thamrin No. 33, Karawaci, Tangerang',
                'experience'       => '6 tahun sebagai Marketing Manager di perusahaan FMCG. Berpengalaman memimpin tim 10 orang dan mengelola budget iklan lebih dari 1 miliar.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => 'https://linkedin.com/in/nurulfd',
            ],
            [
                'name'             => 'Irfan Hakim',
                'email'            => 'irfan.hakim@gmail.com',
                'phone'            => '089012345678',
                'gender'           => 'L',
                'city'             => 'Bogor',
                'zip_code'         => '16111',
                'complete_address' => 'Jl. Pajajaran No. 99, Bogor Tengah, Bogor',
                'experience'       => '2 tahun sebagai Procurement Staff di perusahaan manufaktur. Familiar dengan proses tender, PO, dan vendor management.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => null,
            ],
            [
                'name'             => 'Laila Rahmasari',
                'email'            => 'laila.rahmasari@gmail.com',
                'phone'            => '081122334455',
                'gender'           => 'P',
                'city'             => 'Malang',
                'zip_code'         => '65111',
                'complete_address' => 'Jl. Ijen No. 17, Klojen, Malang',
                'experience'       => 'S2 Bioteknologi. Pengalaman 1 tahun sebagai Research Assistant di laboratorium universitas.',
                'cv_path'          => null,
                'portofolio_path'  => null,
                'web_portofolio_url' => null,
            ],
        ];

        foreach ($candidates as $candidate) {
            $exists = DB::table('candidates')->where('email', $candidate['email'])->exists();
            if (! $exists) {
                DB::table('candidates')->insert(array_merge($candidate, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
