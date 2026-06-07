<?php

namespace App\Services;

use App\Models\Application;
use App\Models\FcmToken;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vacancies;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class NotificationService
{
    public function __construct(protected Messaging $messaging)
    {
    }

    /**
     * Kirim notifikasi ke semua HR saat ada lamaran baru masuk.
     *
     * Dipanggil dari Candidate\ApplicationController::apply()
     */
    public function notifyHrNewApplication(Application $application): void
    {
        $application->loadMissing(['candidate', 'vacancy.position']);

        $candidateName = $application->candidate->name;
        $vacancyTitle  = $application->vacancy->title;
        $positionName  = $application->vacancy->position->position_name;

        $title = '📋 Lamaran Baru';
        $body  = "{$candidateName} melamar ke posisi {$positionName} — {$vacancyTitle}";

        $data = [
            'type'             => 'new_application',
            'application_id'   => (string) $application->id,
            'application_code' => $application->application_code,
            'vacancy_id'       => (string) $application->vacancy_id,
            'vacancy_title'    => $vacancyTitle,
            'candidate_name'   => $candidateName,
        ];

        // Ambil semua user HR yang aktif
        $hrUsers = User::where('role', 'hr')->where('is_active', true)->get();

        if ($hrUsers->isEmpty()) {
            Log::info('[Notification] Tidak ada user HR aktif untuk menerima notifikasi lamaran baru.');
            return;
        }

        // Ambil semua FCM token milik HR
        $tokens = FcmToken::forHr()
            ->whereIn('owner_id', $hrUsers->pluck('id'))
            ->pluck('fcm_token')
            ->toArray();

        // Simpan riwayat notifikasi untuk setiap HR
        foreach ($hrUsers as $hrUser) {
            Notification::create([
                'type'           => 'new_application',
                'title'          => $title,
                'body'           => $body,
                'data'           => $data,
                'recipient_type' => 'hr',
                'recipient_id'   => $hrUser->id,
                'sent_at'        => now(),
            ]);
        }

        // Kirim FCM ke semua token HR
        if (!empty($tokens)) {
            $this->sendToTokens($tokens, $title, $body, $data);
        }
    }

    /**
     * Kirim notifikasi ke semua candidate yang melamar di vacancy tertentu
     * saat DSS MAIRCA selesai dijalankan.
     *
     * Dipanggil dari HR\MAIRCAController::calculate()
     */
    public function notifyDssCompleted(int $vacancyId): void
    {
        // Mencegah duplikasi: Cek apakah notifikasi DSS sudah pernah dikirim untuk vacancy ini
        $alreadySent = Notification::where('type', 'dss_completed')
            ->where('data->vacancy_id', (string) $vacancyId)
            ->exists();

        if ($alreadySent) {
            return;
        }

        $vacancy = Vacancies::with('position')->findOrFail($vacancyId);

        $positionName = $vacancy->position->position_name;
        $vacancyTitle = $vacancy->title;

        $title = '📊 Hasil Seleksi Tersedia';
        $body  = "Hasil seleksi untuk posisi {$positionName} sudah tersedia. Cek status lamaran Anda.";

        // Ambil semua application untuk vacancy ini
        $applications = Application::where('vacancy_id', $vacancyId)->get();

        if ($applications->isEmpty()) {
            Log::info("[Notification] Tidak ada application untuk vacancy #{$vacancyId}.");
            return;
        }

        $applicationIds = $applications->pluck('id')->toArray();

        // Ambil semua FCM token milik candidate yang terkait
        $tokens = FcmToken::forCandidate()
            ->whereIn('owner_id', $applicationIds)
            ->pluck('fcm_token')
            ->toArray();

        // Simpan riwayat notifikasi & kirim per candidate
        foreach ($applications as $application) {
            $candidateData = [
                'type'             => 'dss_completed',
                'vacancy_id'       => (string) $vacancyId,
                'vacancy_title'    => $vacancyTitle,
                'position_name'    => $positionName,
                'application_code' => $application->application_code,
            ];

            Notification::create([
                'type'           => 'dss_completed',
                'title'          => $title,
                'body'           => $body,
                'data'           => $candidateData,
                'recipient_type' => 'candidate',
                'recipient_id'   => $application->id,
                'sent_at'        => now(),
            ]);
        }

        // Kirim FCM ke semua token candidate
        if (!empty($tokens)) {
            $baseData = [
                'type'          => 'dss_completed',
                'vacancy_id'    => (string) $vacancyId,
                'vacancy_title' => $vacancyTitle,
                'position_name' => $positionName,
            ];

            $this->sendToTokens($tokens, $title, $body, $baseData);
        }
    }

    /**
     * Kirim FCM message ke banyak token sekaligus menggunakan multicast.
     *
     * @param  array<string>  $tokens   Daftar FCM token
     * @param  string         $title    Judul notifikasi
     * @param  string         $body     Isi notifikasi
     * @param  array          $data     Data payload tambahan (key-value string)
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (empty($tokens)) {
            return;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(FcmNotification::create($title, $body))
                ->withData($data);

            $report = $this->messaging->sendMulticast($message, $tokens);

            // Log hasil pengiriman
            $successCount = $report->successes()->count();
            $failureCount = $report->failures()->count();

            Log::info("[FCM] Multicast sent: {$successCount} success, {$failureCount} failed.");

            // Cleanup token yang invalid
            if ($failureCount > 0) {
                $this->cleanupInvalidTokens($report);
            }
        } catch (\Throwable $e) {
            Log::error('[FCM] Failed to send multicast: ' . $e->getMessage(), [
                'token_count' => count($tokens),
                'exception'   => $e,
            ]);
        }
    }

    /**
     * Hapus FCM token yang sudah tidak valid (unregistered, expired, dll.)
     * berdasarkan report dari multicast send.
     */
    protected function cleanupInvalidTokens($report): void
    {
        $invalidTokens = [];

        foreach ($report->failures()->getItems() as $failure) {
            $error = $failure->error();

            // Token tidak valid jika device unregistered atau token expired
            if ($error && in_array($error->value, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                $token = $failure->target()->value();
                $invalidTokens[] = $token;
            }
        }

        if (!empty($invalidTokens)) {
            $deletedCount = FcmToken::whereIn('fcm_token', $invalidTokens)->delete();
            Log::info("[FCM] Cleaned up {$deletedCount} invalid token(s).");
        }
    }
}
