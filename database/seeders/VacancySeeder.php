<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\User;
use App\Models\Vacancies;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VacancySeeder extends Seeder
{
    public function run(): void
    {
        // Ambil HR pertama sebagai pembuat lowongan
        $hrUser = User::where('role', 'hr')->first();

        $backendPos  = Position::where('position_name', 'Backend Developer')->first()->id;
        $hrSpecPos   = Position::where('position_name', 'HR Specialist')->first()->id;
        $frontendPos = Position::where('position_name', 'Frontend Developer')->first()->id;

        $vacancies = [
            // Lowongan 1: sudah ditutup (proses rekrutmen selesai)
            [
                'hr_id'       => $hrUser->id,
                'position_id' => $backendPos,
                'title'       => 'Backend Developer – Laravel',
                'description' => 'Kami mencari Backend Developer berpengalaman dengan keahlian di Laravel framework untuk bergabung dengan tim teknologi kami. Kandidat akan bertanggung jawab dalam pengembangan dan pemeliharaan REST API, integrasi database, serta optimasi performa aplikasi.',
                'requirements'=> "- Minimal 2 tahun pengalaman dengan PHP & Laravel\n- Memahami konsep RESTful API dan MVC architecture\n- Familiar dengan MySQL/PostgreSQL\n- Memahami Git version control\n- Kemampuan problem solving yang baik\n- Bersedia bekerja secara hybrid (WFO 3x seminggu)",
                'deadline'    => Carbon::now()->subDays(30)->toDateString(),
                'status'      => 'closed',
            ],

            // Lowongan 2: masih dibuka
            [
                'hr_id'       => $hrUser->id,
                'position_id' => $hrSpecPos,
                'title'       => 'HR Specialist',
                'description' => 'Dibutuhkan HR Specialist untuk mengelola proses rekrutmen, administrasi kepegawaian, dan pengembangan SDM. Posisi ini akan bekerja langsung di bawah HR Manager dan berkolaborasi dengan seluruh departemen.',
                'requirements'=> "- Minimal S1 Psikologi/Manajemen SDM/Hukum\n- Pengalaman minimal 1 tahun di bidang HR\n- Memahami UU Ketenagakerjaan Indonesia\n- Terampil menggunakan HRIS dan Microsoft Office\n- Komunikatif dan detail-oriented\n- Penempatan: Jakarta Selatan",
                'deadline'    => Carbon::now()->addDays(15)->toDateString(),
                'status'      => 'open',
            ],

            // Lowongan 3: baru dibuka
            [
                'hr_id'       => $hrUser->id,
                'position_id' => $frontendPos,
                'title'       => 'Frontend Developer React',
                'description' => 'Bergabunglah dengan tim kami sebagai Frontend Developer yang akan membangun user interface modern menggunakan React.js. Anda akan berkolaborasi dengan tim backend dan UI/UX designer untuk menciptakan pengalaman pengguna terbaik.',
                'requirements'=> "- Minimal 1 tahun pengalaman dengan React.js\n- Menguasai HTML5, CSS3, dan JavaScript ES6+\n- Familiar dengan state management (Redux/Zustand)\n- Memahami responsive design dan cross-browser compatibility\n- Pengalaman dengan REST API consumption\n- Nilai plus: TypeScript, Next.js",
                'deadline'    => Carbon::now()->addDays(7)->toDateString(),
                'status'      => 'open',
            ],
        ];

        foreach ($vacancies as $vacancy) {
            Vacancies::firstOrCreate(
                ['title' => $vacancy['title']],
                $vacancy
            );
        }
    }
}
