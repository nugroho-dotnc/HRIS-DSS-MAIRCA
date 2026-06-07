<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\InterviewSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InterviewSessionSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil interviewer: HR dan Supervisor
        $hrManager  = User::where('email', 'hr1@hris.test')->first()->id;
        $supTeknik  = User::where('email', 'sup1@hris.test')->first()->id;
        $supHrd     = User::where('email', 'sup2@hris.test')->first()->id;

        // Hanya buat sesi untuk aplikasi yang sudah melewati tahap screening:
        // interview_scheduled, interview_done, hired, rejected

        // APP-2026-00001: Ahmad → Backend Dev → hired
        $app1 = Application::where('application_code', 'APP-2026-00001')->first();
        InterviewSession::firstOrCreate(
            ['application_id' => $app1->id, 'interviewer_id' => $supTeknik],
            [
                'application_id' => $app1->id,
                'interviewer_id' => $supTeknik,
                'interview_date' => Carbon::now()->subDays(35)->setHour(10)->setMinute(0),
                'notes'          => 'Kandidat menunjukkan pemahaman mendalam tentang arsitektur Laravel dan RESTful API. Jawaban teknis sangat memuaskan, terutama pada studi kasus optimasi query database.',
            ]
        );

        // APP-2026-00002: Rizky → Backend Dev → rejected
        $app2 = Application::where('application_code', 'APP-2026-00002')->first();
        InterviewSession::firstOrCreate(
            ['application_id' => $app2->id, 'interviewer_id' => $supTeknik],
            [
                'application_id' => $app2->id,
                'interviewer_id' => $supTeknik,
                'interview_date' => Carbon::now()->subDays(34)->setHour(14)->setMinute(0),
                'notes'          => 'Kandidat masih junior, jawaban teknis cukup namun belum memiliki pengalaman produksi yang memadai. Potensi bagus untuk posisi junior di masa depan.',
            ]
        );

        // APP-2026-00003: Siti → HR Specialist → interview_done
        $app3 = Application::where('application_code', 'APP-2026-00003')->first();
        InterviewSession::firstOrCreate(
            ['application_id' => $app3->id, 'interviewer_id' => $hrManager],
            [
                'application_id' => $app3->id,
                'interviewer_id' => $hrManager,
                'interview_date' => Carbon::now()->subDays(4)->setHour(9)->setMinute(30),
                'notes'          => 'Kandidat memiliki sertifikasi CHRP dan pengalaman solid di HR. Komunikasi sangat baik, memahami regulasi ketenagakerjaan Indonesia dengan detail.',
            ]
        );

        // APP-2026-00004: Dewi → HR Specialist → interview_scheduled (belum dilaksanakan)
        $app4 = Application::where('application_code', 'APP-2026-00004')->first();
        InterviewSession::firstOrCreate(
            ['application_id' => $app4->id, 'interviewer_id' => $supHrd],
            [
                'application_id' => $app4->id,
                'interviewer_id' => $supHrd,
                'interview_date' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0),
                'notes'          => 'Jadwal interview telah dikonfirmasi melalui email. Kandidat akan diwawancarai secara online via Google Meet.',
            ]
        );
    }
}
