<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * GET /api/hr/applications
     * List semua lamaran masuk dengan filter status / vacancy.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Application::with([
            'candidate',
            'vacancy.position.department',
        ]);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('vacancy_id')) {
            $query->where('vacancy_id', $request->vacancy_id);
        }

        if ($request->has('search')) {
            $query->whereHas('candidate', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $applications = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $applications,
        ]);
    }

    /**
     * GET /api/hr/applications/{id}
     * Detail lamaran lengkap beserta data kandidat dan riwayat interview.
     */
    public function show(string $id): JsonResponse
    {
        $application = Application::with([
            'candidate',
            'vacancy.position.department',
            'vacancy.position.recruitment_criteria.likertScales',
            'interviewSessions.interviewer',
            'interviewSessions.scores.criteria',
            'result',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $application,
        ]);
    }

    /**
     * PATCH /api/hr/applications/{id}/screening
     * Pindahkan lamaran ke status 'screening' — HR mulai mereview.
     */
    public function moveToScreening(string $id): JsonResponse
    {
        $application = Application::findOrFail($id);

        if ($application->status !== 'applied') {
            return response()->json([
                'success' => false,
                'message' => "Hanya lamaran berstatus 'applied' yang dapat dipindahkan ke screening. Status saat ini: {$application->status}.",
            ], 422);
        }

        $application->status = 'screening';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Lamaran dipindahkan ke screening.',
            'data'    => $application->load('candidate', 'vacancy'),
        ]);
    }

    /**
     * PATCH /api/hr/applications/{id}/reject
     * Tolak lamaran di tahap manapun (applied / screening / interview_scheduled).
     */
    public function reject(string $id): JsonResponse
    {
        $application = Application::findOrFail($id);

        $allowedStatuses = ['applied', 'screening', 'interview_scheduled'];

        if (! in_array($application->status, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => "Lamaran berstatus '{$application->status}' tidak dapat ditolak pada tahap ini.",
            ], 422);
        }

        $application->status = 'rejected';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil ditolak.',
            'data'    => $application->load('candidate', 'vacancy'),
        ]);
    }
}
