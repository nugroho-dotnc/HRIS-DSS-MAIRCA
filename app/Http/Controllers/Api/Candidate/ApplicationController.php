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
use OpenApi\Attributes as OA;

class ApplicationController extends Controller
{
    #[OA\Post(
        path: '/apply',
        summary: 'Apply ke lowongan pekerjaan',
        description: 'Kandidat mengisi data diri sekaligus apply ke vacancy tanpa perlu login. Sistem membuat record candidate (jika belum ada), application, dan menerbitkan Application Code.',
        tags: ['Public - Candidate'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'email', 'phone', 'gender', 'city', 'zip_code', 'complete_address', 'experience', 'vacancy_id'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                        new OA\Property(property: 'phone', type: 'string', example: '081234567890'),
                        new OA\Property(property: 'gender', type: 'string', enum: ['L', 'P'], example: 'L'),
                        new OA\Property(property: 'city', type: 'string', example: 'Jakarta Raya'),
                        new OA\Property(property: 'zip_code', type: 'string', example: '12345'),
                        new OA\Property(property: 'complete_address', type: 'string', example: 'Jl. Sudirman No. 1'),
                        new OA\Property(property: 'experience', type: 'string', example: '3 tahun di Software Engineering'),
                        new OA\Property(property: 'web_portofolio_url', type: 'string', format: 'url', nullable: true),
                        new OA\Property(property: 'vacancy_id', type: 'integer', example: 1),
                        new OA\Property(property: 'cv', type: 'string', format: 'binary', description: 'File CV (PDF/DOC/DOCX)'),
                        new OA\Property(property: 'portofolio', type: 'string', format: 'binary', description: 'File Portofolio (PDF/ZIP)')
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Lamaran berhasil dikirim',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Lamaran berhasil dikirim!'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'application_code', type: 'string', example: 'APP-2024-ABCDEF'),
                                new OA\Property(property: 'vacancy_title', type: 'string', example: 'Senior Software Engineer'),
                                new OA\Property(property: 'candidate_name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'status', type: 'string', example: 'applied'),
                                new OA\Property(property: 'note', type: 'string', example: 'Simpan Application Code Anda untuk tracking status lamaran.')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi gagal atau kandidat sudah apply', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            // Data diri kandidat
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:L,P',
            'city' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'complete_address' => 'required|string',
            'experience' => 'required|string',
            'web_portofolio_url' => 'nullable|url|max:255',
            // Dokumen
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // max 5MB
            'portofolio' => 'nullable|file|mimes:pdf,zip|max:10240',     // max 10MB
            // Vacancy tujuan
            'vacancy_id' => 'required|exists:vacancies,id',
        ]);

        // Cek vacancy masih open
        $vacancy = Vacancies::where('id', $request->vacancy_id)
            ->where('status', 'open')
            ->where('deadline', '>=', now()->toDateString())
            ->first();

        if (!$vacancy) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak tersedia atau sudah melewati deadline.',
            ], 422);
        }

        // Cari atau buat kandidat berdasarkan email
        $candidate = Candidate::firstOrNew(['email' => $request->email]);

        // Update data diri
        $candidate->fill($request->only([
            'name',
            'email',
            'phone',
            'gender',
            'city',
            'zip_code',
            'complete_address',
            'experience',
            'web_portofolio_url',
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
            'candidate_id' => $candidate->id,
            'vacancy_id' => $request->vacancy_id,
            'status' => 'applied',
            'application_code' => $applicationCode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lamaran berhasil dikirim!',
            'data' => [
                'application_code' => $applicationCode,
                'vacancy_title' => $vacancy->title,
                'candidate_name' => $candidate->name,
                'status' => 'applied',
                'note' => 'Simpan Application Code Anda untuk tracking status lamaran.',
            ],
        ], 201);
    }

    #[OA\Get(
        path: '/track/{applicationCode}',
        summary: 'Lacak status lamaran',
        description: 'Tracking status lamaran via Application Code tanpa perlu login.',
        tags: ['Public - Candidate'],
        parameters: [
            new OA\Parameter(name: 'applicationCode', in: 'path', required: true, description: 'Kode Lamaran', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data lamaran ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'application_code', type: 'string', example: 'APP-2024-ABCDEF'),
                                new OA\Property(property: 'status', type: 'string', example: 'screening'),
                                new OA\Property(property: 'status_description', type: 'string', example: 'Lamaran Anda sedang direview oleh tim HR.'),
                                new OA\Property(property: 'applied_at', type: 'string', format: 'date', example: '2024-01-01'),
                                new OA\Property(property: 'vacancy', type: 'object'),
                                new OA\Property(property: 'candidate_name', type: 'string', example: 'Budi Santoso')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Application Code tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function track(string $applicationCode): JsonResponse
    {
        $application = Application::with([
            'vacancy.position.department',
            'candidate',
        ])->where('application_code', $applicationCode)->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application Code tidak ditemukan. Pastikan kode yang Anda masukkan benar.',
            ], 404);
        }

        $statusLabels = [
            'applied' => 'Lamaran diterima dan sedang menunggu review.',
            'screening' => 'Lamaran Anda sedang direview oleh tim HR.',
            'interview_scheduled' => 'Selamat! Anda dijadwalkan untuk sesi interview.',
            'interview_done' => 'Interview selesai. Menunggu keputusan final.',
            'hired' => '🎉 Selamat! Anda dinyatakan DITERIMA.',
            'rejected' => 'Maaf, lamaran Anda tidak dapat dilanjutkan pada tahap ini.',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'application_code' => $applicationCode,
                'status' => $application->status,
                'status_description' => $statusLabels[$application->status] ?? '',
                'applied_at' => $application->created_at->toDateString(),
                'vacancy' => [
                    'title' => $application->vacancy->title,
                    'position' => $application->vacancy->position->position_name,
                    'department' => $application->vacancy->position->department->department_name,
                ],
                'candidate_name' => $application->candidate->name,
            ],
        ]);
    }

    #[OA\Get(
        path: '/candidate/applications',
        summary: 'Daftar lamaran saya',
        description: 'List lamaran milik kandidat yang sedang login.',
        security: [['sanctum' => []]],
        tags: ['Candidate - Applications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar lamaran berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'candidate_name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'total', type: 'integer', example: 2),
                                new OA\Property(property: 'applications', type: 'array', items: new OA\Items(type: 'object'))
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Profil kandidat tidak ditemukan', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function myApplications(Request $request): JsonResponse
    {
        $user = $request->user();
        $candidate = Candidate::where('email', $user->email)->first();

        if (!$candidate) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum memiliki profil kandidat. Silakan apply ke lowongan terlebih dahulu.',
            ], 404);
        }

        $applications = Application::with(['vacancy.position.department'])
            ->where('candidate_id', $candidate->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($app) => [
                'application_code' => $app->application_code,
                'vacancy_title' => $app->vacancy->title,
                'position' => $app->vacancy->position->position_name,
                'department' => $app->vacancy->position->department->department_name,
                'status' => $app->status,
                'applied_at' => $app->created_at->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'candidate_name' => $candidate->name,
                'total' => $applications->count(),
                'applications' => $applications,
            ],
        ]);
    }
}
