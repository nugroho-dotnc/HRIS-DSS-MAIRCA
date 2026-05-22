<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    /**
     * POST /api/apply
     * Apply ke vacancy — PUBLIC (tanpa login).
     * Kandidat mengisi data diri sekaligus apply ke vacancy.
     * Sistem membuat record candidate (jika belum ada), application, dan menerbitkan Application Code.
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            // Data diri kandidat
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255',
            'phone'             => 'required|string|max:20',
            'gender'            => 'required|in:L,P',
            'city'              => 'required|string|max:255',
            'zip_code'          => 'required|string|max:10',
            'complete_address'  => 'required|string',
            'experience'        => 'required|string',
            'web_portofolio_url'=> 'nullable|url|max:255',
            // Dokumen
            'cv'                => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'portofolio'        => 'nullable|file|mimes:pdf,zip|max:10240',     // max 10MB
            // Vacancy tujuan
            'vacancy_id'        => 'required|exists:vacancies,id',
        ]);

        // Cek vacancy masih open
        $vacancy = Vacancies::where('id', $request->vacancy_id)
            ->where('status', 'open')
            ->where('deadline', '>=', now()->toDateString())
            ->first();

        if (! $vacancy) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak tersedia atau sudah melewati deadline.',
            ], 422);
        }

        // Cari atau buat kandidat berdasarkan email
        $candidate = Candidate::firstOrNew(['email' => $request->email]);

        // Update data diri
        $candidate->fill($request->only([
            'name', 'email', 'phone', 'gender', 'city', 'zip_code',
            'complete_address', 'experience', 'web_portofolio_url',
        ]));

        // Upload CV
        if ($request->hasFile('cv')) {
            if ($candidate->cv_path) {
                Storage::disk('public')->delete($candidate->cv_path);
            }
            $candidate->cv_path = $request->file('cv')->store('cv', 'public');
        }

        // Upload Portofolio
        if ($request->hasFile('portofolio')) {
            if ($candidate->portofolio_path) {
                Storage::disk('public')->delete($candidate->portofolio_path);
            }
            $candidate->portofolio_path = $request->file('portofolio')->store('portofolio', 'public');
        }

        $candidate->save();

        // Cek apakah kandidat sudah pernah apply ke vacancy yang sama
        $alreadyApplied = Application::where('candidate_id', $candidate->id)
            ->where('vacancy_id', $request->vacancy_id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah melamar ke lowongan ini.',
            ], 422);
        }

        // Generate Application Code unik
        $applicationCode = 'APP-' . now()->year . '-' . strtoupper(Str::random(6));

        // Pastikan kode unik
        while (Application::where('application_code', $applicationCode)->exists()) {
            $applicationCode = 'APP-' . now()->year . '-' . strtoupper(Str::random(6));
        }

        $application = Application::create([
            'candidate_id'     => $candidate->id,
            'vacancy_id'       => $request->vacancy_id,
            'status'           => 'applied',
            'application_code' => $applicationCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil dikirim!',
            'data'    => [
                'application_code'  => $applicationCode,
                'vacancy_title'     => $vacancy->title,
                'candidate_name'    => $candidate->name,
                'status'            => 'applied',
                'note'              => 'Simpan Application Code Anda untuk tracking status lamaran.',
            ],
        ], 201);
    }

    /**
     * GET /api/track/{applicationCode}
     * Tracking status lamaran via Application Code — PUBLIC (tanpa login).
     * Kandidat hanya bisa lihat status lamarannya sendiri.
     */
    public function track(string $applicationCode): JsonResponse
    {
        $application = Application::with([
            'vacancy.position.department',
            'candidate',
        ])->where('application_code', $applicationCode)->first();

        if (! $application) {
            return response()->json([
                'success' => false,
                'message' => 'Application Code tidak ditemukan. Pastikan kode yang Anda masukkan benar.',
            ], 404);
        }

        $statusLabels = [
            'applied'              => 'Lamaran diterima dan sedang menunggu review.',
            'screening'            => 'Lamaran Anda sedang direview oleh tim HR.',
            'interview_scheduled'  => 'Selamat! Anda dijadwalkan untuk sesi interview.',
            'interview_done'       => 'Interview selesai. Menunggu keputusan final.',
            'hired'                => '🎉 Selamat! Anda dinyatakan DITERIMA.',
            'rejected'             => 'Maaf, lamaran Anda tidak dapat dilanjutkan pada tahap ini.',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'application_code'  => $applicationCode,
                'status'            => $application->status,
                'status_description'=> $statusLabels[$application->status] ?? '',
                'applied_at'        => $application->created_at->toDateString(),
                'vacancy'           => [
                    'title'      => $application->vacancy->title,
                    'position'   => $application->vacancy->position->position_name,
                    'department' => $application->vacancy->position->department->department_name,
                ],
                'candidate_name' => $application->candidate->name,
            ],
        ]);
    }

    /**
     * GET /api/candidate/applications
     * List lamaran milik kandidat yang sedang login.
     */
    public function myApplications(Request $request): JsonResponse
    {
        $user      = $request->user();
        $candidate = Candidate::where('email', $user->email)->first();

        if (! $candidate) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki profil kandidat. Silakan apply ke lowongan terlebih dahulu.',
            ], 404);
        }

        $applications = Application::with(['vacancy.position.department'])
            ->where('candidate_id', $candidate->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($app) => [
                'application_code' => $app->application_code,
                'vacancy_title'    => $app->vacancy->title,
                'position'         => $app->vacancy->position->position_name,
                'department'       => $app->vacancy->position->department->department_name,
                'status'           => $app->status,
                'applied_at'       => $app->created_at->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'candidate_name' => $candidate->name,
                'total'          => $applications->count(),
                'applications'   => $applications,
            ],
        ]);
    }
}
