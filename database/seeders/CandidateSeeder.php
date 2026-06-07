<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            // Kandidat 1: Backend dev berpengalaman
            [
                'name'               => 'Ahmad Fauzan',
                'email'              => 'ahmad.fauzan@gmail.com',
                'phone'              => '0812-3456-7890',
                'gender'             => 'L',
                'city'               => 'Jakarta',
                'zip_code'           => '12950',
                'cv_path'            => 'cv/candidate_1.pdf',
                'portofolio_path'    => 'portofolio/candidate_1.pdf',
                'web_portofolio_url' => 'https://ahmadfauzan.dev',
                'complete_address'   => 'Jl. Kemang Raya No. 45, RT 003/RW 007, Kel. Bangka, Kec. Mampang Prapatan, Jakarta Selatan 12950',
                'experience'        => 'Berpengalaman 3 tahun sebagai Backend Developer di PT Teknologi Nusantara. Mengembangkan REST API menggunakan Laravel dan mengelola database MySQL untuk aplikasi e-commerce dengan lebih dari 50.000 pengguna aktif. Sebelumnya magang di startup fintech selama 6 bulan.',
            ],

            // Kandidat 2: HR background
            [
                'name'               => 'Siti Nurhaliza',
                'email'              => 'siti.nurhaliza@gmail.com',
                'phone'              => '0857-1234-5678',
                'gender'             => 'P',
                'city'               => 'Bandung',
                'zip_code'           => '40132',
                'cv_path'            => 'cv/candidate_2.pdf',
                'portofolio_path'    => null,
                'web_portofolio_url' => null,
                'complete_address'   => 'Jl. Dago Atas No. 12, RT 001/RW 005, Kel. Dago, Kec. Coblong, Bandung 40132',
                'experience'        => 'Lulusan S1 Psikologi Universitas Padjadjaran dengan pengalaman 2 tahun sebagai HR Generalist di PT Sejahtera Abadi. Menangani proses rekrutmen end-to-end, onboarding karyawan baru, dan administrasi BPJS. Memiliki sertifikasi CHRP dari BNSP.',
            ],

            // Kandidat 3: Junior dev
            [
                'name'               => 'Rizky Pratama',
                'email'              => 'rizky.pratama@gmail.com',
                'phone'              => '0878-9012-3456',
                'gender'             => 'L',
                'city'               => 'Surabaya',
                'zip_code'           => '60271',
                'cv_path'            => 'cv/candidate_3.pdf',
                'portofolio_path'    => 'portofolio/candidate_3.pdf',
                'web_portofolio_url' => 'https://rizky-dev.netlify.app',
                'complete_address'   => 'Jl. Raya Darmo No. 78, RT 005/RW 002, Kel. Darmo, Kec. Wonokromo, Surabaya 60271',
                'experience'        => 'Fresh graduate Teknik Informatika ITS dengan pengalaman magang 6 bulan di Tokopedia sebagai Backend Engineer Intern. Mengerjakan microservice dengan Go dan Laravel. Aktif berkontribusi di open source dan memiliki beberapa project personal di GitHub.',
            ],

            // Kandidat 4: HR junior
            [
                'name'               => 'Dewi Anggraini',
                'email'              => 'dewi.anggraini@gmail.com',
                'phone'              => '0813-5678-9012',
                'gender'             => 'P',
                'city'               => 'Yogyakarta',
                'zip_code'           => '55281',
                'cv_path'            => 'cv/candidate_4.pdf',
                'portofolio_path'    => null,
                'web_portofolio_url' => null,
                'complete_address'   => 'Jl. Malioboro No. 56, RT 002/RW 004, Kel. Suryatmajan, Kec. Danurejan, Yogyakarta 55281',
                'experience'        => 'Lulusan S1 Manajemen UGM, memiliki pengalaman 1 tahun sebagai Admin HR di CV Mandiri Jaya. Menangani absensi karyawan, penggajian, dan administrasi cuti. Familiar dengan sistem HRIS dan Microsoft Excel tingkat lanjut.',
            ],

            // Kandidat 5: Fullstack/frontend
            [
                'name'               => 'Muhammad Ilham',
                'email'              => 'muhammad.ilham@gmail.com',
                'phone'              => '0856-7890-1234',
                'gender'             => 'L',
                'city'               => 'Depok',
                'zip_code'           => '16424',
                'cv_path'            => 'cv/candidate_5.pdf',
                'portofolio_path'    => 'portofolio/candidate_5.pdf',
                'web_portofolio_url' => 'https://ilham-portfolio.vercel.app',
                'complete_address'   => 'Jl. Margonda Raya No. 100, RT 006/RW 003, Kel. Kemiri Muka, Kec. Beji, Depok 16424',
                'experience'        => 'Frontend Developer dengan 2 tahun pengalaman di PT Digital Kreasi. Spesialisasi di React.js dan Next.js. Pernah memimpin redesign UI dashboard internal yang meningkatkan user engagement sebesar 40%. Berpengalaman dengan Tailwind CSS, TypeScript, dan integrasi REST API.',
            ],
        ];

        foreach ($candidates as $candidateData) {
            Candidate::firstOrCreate(
                ['email' => $candidateData['email']],
                $candidateData
            );
        }
    }
}
