<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FcmToken;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FcmTokenController extends Controller
{
    // =========================================================================
    // HR — Register FCM Token (auth required)
    // =========================================================================

    #[OA\Post(
        path: '/hr/fcm-tokens',
        summary: 'Register FCM Token (HR)',
        description: 'Mendaftarkan atau memperbarui FCM token untuk user HR yang sedang login. Dipanggil saat Flutter app start atau saat token refresh.',
        security: [['sanctum' => []]],
        tags: ['HR - FCM Token'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['fcm_token'],
                properties: [
                    new OA\Property(property: 'fcm_token', type: 'string', example: 'dGVzdC10b2tlbi0xMjM0NTY...', description: 'Firebase Cloud Messaging token dari device'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'FCM token berhasil didaftarkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'FCM token berhasil didaftarkan.'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function registerHr(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string|max:512',
        ]);

        $user = $request->user();

        FcmToken::updateOrCreate(
            [
                'owner_type' => 'hr',
                'owner_id'   => $user->id,
                'fcm_token'  => $request->fcm_token,
            ],
            [
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token berhasil didaftarkan.',
        ]);
    }

    // =========================================================================
    // Candidate — Register FCM Token (public, via application_code)
    // =========================================================================

    #[OA\Post(
        path: '/fcm-tokens/candidate',
        summary: 'Register FCM Token (Candidate)',
        description: 'Mendaftarkan FCM token untuk candidate menggunakan application_code sebagai identifier. Tidak memerlukan login. Dipanggil dari Flutter app saat candidate membuka tracking page.',
        tags: ['Public - FCM Token'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['fcm_token', 'application_code'],
                properties: [
                    new OA\Property(property: 'fcm_token', type: 'string', example: 'dGVzdC10b2tlbi0xMjM0NTY...', description: 'Firebase Cloud Messaging token dari device'),
                    new OA\Property(property: 'application_code', type: 'string', example: 'APP-2026-ABCXYZ', description: 'Kode lamaran unik yang dimiliki candidate'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'FCM token berhasil didaftarkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'FCM token berhasil didaftarkan.'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Application code tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function registerCandidate(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token'        => 'required|string|max:512',
            'application_code' => 'required|string|max:20',
        ]);

        $application = Application::where('application_code', $request->application_code)->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application code tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        FcmToken::updateOrCreate(
            [
                'owner_type' => 'candidate',
                'owner_id'   => $application->id,
                'fcm_token'  => $request->fcm_token,
            ],
            [
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token berhasil didaftarkan.',
        ]);
    }

    // =========================================================================
    // Hapus FCM Token (public — untuk logout/uninstall)
    // =========================================================================

    #[OA\Delete(
        path: '/fcm-tokens',
        summary: 'Hapus FCM Token',
        description: 'Menghapus FCM token dari database. Dipanggil saat user logout atau uninstall app. Endpoint ini bersifat publik karena candidate tidak memiliki login.',
        tags: ['Public - FCM Token'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['fcm_token'],
                properties: [
                    new OA\Property(property: 'fcm_token', type: 'string', example: 'dGVzdC10b2tlbi0xMjM0NTY...', description: 'FCM token yang akan dihapus'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'FCM token berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'FCM token berhasil dihapus.'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string|max:512',
        ]);

        $deleted = FcmToken::where('fcm_token', $request->fcm_token)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted > 0
                ? 'FCM token berhasil dihapus.'
                : 'FCM token tidak ditemukan, tidak ada yang dihapus.',
        ]);
    }

    // =========================================================================
    // Notification History — Ambil riwayat notifikasi (HR)
    // =========================================================================

    #[OA\Get(
        path: '/hr/notifications',
        summary: 'Riwayat Notifikasi HR',
        description: 'Mengambil daftar notifikasi yang pernah diterima oleh user HR yang sedang login. Mendukung pagination.',
        security: [['sanctum' => []]],
        tags: ['HR - Notifications'],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah item per halaman (default: 15)', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', required: false, description: 'Nomor halaman', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar notifikasi berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Daftar notifikasi berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function notificationsHr(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = $request->integer('per_page', 15);

        $notifications = Notification::forRecipient('hr', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil.',
            'data'    => [
                'unread_count'  => Notification::forRecipient('hr', $user->id)->unread()->count(),
                'notifications' => $notifications,
            ],
        ]);
    }

    // =========================================================================
    // Notification History — Ambil riwayat notifikasi (Candidate)
    // =========================================================================

    #[OA\Get(
        path: '/notifications/candidate/{applicationCode}',
        summary: 'Riwayat Notifikasi Candidate',
        description: 'Mengambil daftar notifikasi untuk candidate berdasarkan application_code. Tidak memerlukan login.',
        tags: ['Public - Notifications'],
        parameters: [
            new OA\Parameter(name: 'applicationCode', in: 'path', required: true, description: 'Kode lamaran unik', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar notifikasi berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Daftar notifikasi berhasil diambil.'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Application code tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function notificationsCandidate(string $applicationCode): JsonResponse
    {
        $application = Application::where('application_code', $applicationCode)->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application code tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $notifications = Notification::forRecipient('candidate', $application->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil.',
            'data'    => [
                'unread_count'  => Notification::forRecipient('candidate', $application->id)->unread()->count(),
                'notifications' => $notifications,
            ],
        ]);
    }

    // =========================================================================
    // Mark notification as read
    // =========================================================================

    #[OA\Patch(
        path: '/notifications/{id}/read',
        summary: 'Tandai notifikasi sebagai sudah dibaca',
        description: 'Mengubah status notifikasi menjadi sudah dibaca.',
        tags: ['Public - Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID notifikasi', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Notifikasi berhasil ditandai sebagai dibaca',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Notifikasi berhasil ditandai sebagai dibaca.'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Notifikasi tidak ditemukan'),
        ]
    )]
    public function markAsRead(int $id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca.',
        ]);
    }
}
