<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vacancies;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $hrManager = User::where('email', 'hr1@hris.test')->first();

        $vacancy1 = Vacancies::where('title', 'Backend Developer – Laravel')->first();
        $vacancy2 = Vacancies::where('title', 'HR Specialist')->first();
        $vacancy3 = Vacancies::where('title', 'Frontend Developer React')->first();

        $app1 = Application::where('application_code', 'APP-2026-00001')->first();
        $app3 = Application::where('application_code', 'APP-2026-00003')->first();
        $app6 = Application::where('application_code', 'APP-2026-00006')->first();
        $app7 = Application::where('application_code', 'APP-2026-00007')->first();

        $notifications = [
            // ── Notifikasi ke HR: ada lamaran baru masuk ──────────

            // 1. Notifikasi lamaran baru (sudah dibaca)
            [
                'type'           => 'new_application',
                'title'          => 'Lamaran Baru Diterima',
                'body'           => 'Muhammad Ilham telah melamar untuk posisi Frontend Developer React.',
                'data'           => json_encode([
                    'application_code' => 'APP-2026-00006',
                    'vacancy_id'       => $vacancy3->id,
                    'candidate_name'   => 'Muhammad Ilham',
                ]),
                'recipient_type' => 'hr',
                'recipient_id'   => $hrManager->id,
                'is_read'        => true,
                'sent_at'        => Carbon::now()->subDays(2),
                'created_at'     => Carbon::now()->subDays(2),
                'updated_at'     => Carbon::now()->subDays(2),
            ],

            // 2. Notifikasi lamaran baru (belum dibaca)
            [
                'type'           => 'new_application',
                'title'          => 'Lamaran Baru Diterima',
                'body'           => 'Rizky Pratama telah melamar untuk posisi Frontend Developer React.',
                'data'           => json_encode([
                    'application_code' => 'APP-2026-00007',
                    'vacancy_id'       => $vacancy3->id,
                    'candidate_name'   => 'Rizky Pratama',
                ]),
                'recipient_type' => 'hr',
                'recipient_id'   => $hrManager->id,
                'is_read'        => false,
                'sent_at'        => Carbon::now()->subDays(1),
                'created_at'     => Carbon::now()->subDays(1),
                'updated_at'     => Carbon::now()->subDays(1),
            ],

            // 3. Notifikasi lamaran baru untuk lowongan HR (belum dibaca)
            [
                'type'           => 'new_application',
                'title'          => 'Lamaran Baru Diterima',
                'body'           => 'Siti Nurhaliza telah melamar untuk posisi HR Specialist.',
                'data'           => json_encode([
                    'application_code' => 'APP-2026-00003',
                    'vacancy_id'       => $vacancy2->id,
                    'candidate_name'   => 'Siti Nurhaliza',
                ]),
                'recipient_type' => 'hr',
                'recipient_id'   => $hrManager->id,
                'is_read'        => false,
                'sent_at'        => Carbon::now()->subDays(10),
                'created_at'     => Carbon::now()->subDays(10),
                'updated_at'     => Carbon::now()->subDays(10),
            ],

            // ── Notifikasi ke Candidate: hasil DSS selesai ───────

            // 4. Notif ke Ahmad: DSS selesai → hired
            [
                'type'           => 'dss_completed',
                'title'          => 'Hasil Seleksi Tersedia',
                'body'           => 'Selamat! Anda dinyatakan LOLOS seleksi untuk posisi Backend Developer – Laravel. Silakan cek detail hasil seleksi Anda.',
                'data'           => json_encode([
                    'application_code' => 'APP-2026-00001',
                    'vacancy_id'       => $vacancy1->id,
                    'result'           => 'hired',
                ]),
                'recipient_type' => 'candidate',
                'recipient_id'   => $app1->id,
                'is_read'        => false,
                'sent_at'        => Carbon::now()->subDays(30),
                'created_at'     => Carbon::now()->subDays(30),
                'updated_at'     => Carbon::now()->subDays(30),
            ],

            // 5. Notif ke Rizky: DSS selesai → rejected
            [
                'type'           => 'dss_completed',
                'title'          => 'Hasil Seleksi Tersedia',
                'body'           => 'Terima kasih atas partisipasi Anda dalam seleksi Backend Developer – Laravel. Mohon maaf, Anda belum lolos pada kesempatan ini.',
                'data'           => json_encode([
                    'application_code' => 'APP-2026-00002',
                    'vacancy_id'       => $vacancy1->id,
                    'result'           => 'rejected',
                ]),
                'recipient_type' => 'candidate',
                'recipient_id'   => Application::where('application_code', 'APP-2026-00002')->first()->id,
                'is_read'        => false,
                'sent_at'        => Carbon::now()->subDays(30),
                'created_at'     => Carbon::now()->subDays(30),
                'updated_at'     => Carbon::now()->subDays(30),
            ],
        ];

        foreach ($notifications as $notifData) {
            Notification::firstOrCreate(
                [
                    'type'           => $notifData['type'],
                    'recipient_type' => $notifData['recipient_type'],
                    'recipient_id'   => $notifData['recipient_id'],
                    'title'          => $notifData['title'],
                ],
                $notifData
            );
        }
    }
}
