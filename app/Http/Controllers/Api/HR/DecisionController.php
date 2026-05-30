<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Employee;
use App\Models\RecruitmentResult;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class DecisionController extends Controller
{
    #[OA\Post(
        path: '/hr/decisions/{applicationId}',
        summary: 'Tetapkan keputusan final',
        description: "Tetapkan keputusan final: 'hired' atau 'rejected'. HR dapat override dari hasil ranking MAIRCA.",
        security: [['sanctum' => []]],
        tags: ['HR - Decisions'],
        parameters: [
            new OA\Parameter(name: 'applicationId', in: 'path', required: true, description: 'ID lamaran', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['decission'],
                properties: [
                    new OA\Property(property: 'decission', type: 'string', enum: ['hired', 'rejected'], example: 'hired'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Keputusan berhasil ditetapkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Kandidat dinyatakan DITERIMA. Silakan lanjutkan proses onboarding.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'application_id', type: 'integer', example: 1),
                                new OA\Property(property: 'candidate_name', type: 'string', example: 'Budi Santoso'),
                                new OA\Property(property: 'decission', type: 'string', example: 'hired'),
                                new OA\Property(property: 'mairca_ranking', type: 'integer', example: 1),
                                new OA\Property(property: 'mairca_score', type: 'number', format: 'float', example: 0.95)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lamaran tidak ditemukan'),
            new OA\Response(response: 422, description: 'Status lamaran tidak valid atau validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function decide(Request $request, string $applicationId): JsonResponse
    {
        $application = Application::with(['candidate', 'vacancy.position', 'result'])
            ->findOrFail($applicationId);

        if ($application->status !== 'interview_done') {
            return response()->json([
                'success' => false,
                'message' => "Keputusan hanya bisa dibuat untuk lamaran berstatus 'interview_done'. Status saat ini: {$application->status}.",
                'data' => null,
            ], 422);
        }

        if (!$application->result) {
            return response()->json([
                'success' => false,
                'message' => 'Hasil kalkulasi MAIRCA belum ada untuk lamaran ini. Jalankan kalkulasi MAIRCA terlebih dahulu.',
                'data' => null,
            ], 422);
        }

        $request->validate([
            'decission' => ['required', Rule::in(['hired', 'rejected'])],
            'notes' => 'nullable|string',
        ]);

        // Update keputusan di recruitment_results
        $application->result->decission = $request->decission;
        $application->result->save();

        // Update status application
        $application->status = $request->decission;
        $application->save();

        $message = $request->decission === 'hired'
            ? 'Kandidat dinyatakan DITERIMA. Silakan lanjutkan proses onboarding.'
            : 'Kandidat dinyatakan DITOLAK.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'application_id' => $application->id,
                'candidate_name' => $application->candidate->name,
                'decission' => $request->decission,
                'mairca_ranking' => $application->result->ranking,
                'mairca_score' => $application->result->final_score,
            ],
        ]);
    }

    #[OA\Post(
        path: '/hr/onboarding/{applicationId}',
        summary: 'Proses Onboarding',
        description: "Onboarding: buat data Employee dari kandidat yang sudah 'hired'. Otomatis membuat user dengan role 'employee'.",
        security: [['sanctum' => []]],
        tags: ['HR - Decisions'],
        parameters: [
            new OA\Parameter(name: 'applicationId', in: 'path', required: true, description: 'ID lamaran', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['department_id', 'position_id', 'supervisor_id', 'join_date', 'contract_status'],
                properties: [
                    new OA\Property(property: 'department_id', type: 'integer', example: 1),
                    new OA\Property(property: 'position_id', type: 'integer', example: 1),
                    new OA\Property(property: 'supervisor_id', type: 'integer', example: 2),
                    new OA\Property(property: 'join_date', type: 'string', format: 'date', example: '2024-01-01'),
                    new OA\Property(property: 'contract_status', type: 'string', enum: ['permanent', 'contract', 'probation'], example: 'probation')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Onboarding berhasil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Onboarding berhasil. Budi Santoso telah menjadi karyawan.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'user', type: 'object'),
                                new OA\Property(property: 'employee', type: 'object'),
                                new OA\Property(property: 'note', type: 'string')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Lamaran tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal atau status tidak valid', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function onboarding(Request $request, string $applicationId): JsonResponse
    {
        $application = Application::with(['candidate', 'vacancy.position'])
            ->findOrFail($applicationId);

        if ($application->status !== 'hired') {
            return response()->json([
                'success' => false,
                'message' => "Onboarding hanya untuk kandidat dengan status 'hired'. Status saat ini: {$application->status}.",
                'data' => null,
            ], 422);
        }

        $candidate = $application->candidate;

        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'supervisor_id' => 'required|exists:employees,id',
            'join_date' => 'required|date',
            'contract_status' => ['required', Rule::in(['permanent', 'contract', 'probation'])],
        ]);

        // Cek apakah user sudah ada (by email)
        $user = User::where('email', $candidate->email)->first();

        if ($user) {
            // Update role ke employee jika sebelumnya candidate
            if ($user->role === 'candidate') {
                $user->role = 'employee';
                $user->save();
            }
        } else {
            // Buat user baru dengan role employee
            $user = User::create([
                'name' => $candidate->name,
                'email' => $candidate->email,
                'password' => Hash::make('password'), // Default password — harus diganti user
                'role' => 'employee',
                'status' => 'active',
            ]);
        }

        // Cek apakah employee record sudah ada
        $existingEmployee = Employee::where('user_id', $user->id)->first();
        if ($existingEmployee) {
            return response()->json([
                'success' => false,
                'message' => 'Data employee untuk user ini sudah ada.',
                'data' => $existingEmployee->load(['user', 'department', 'position', 'supervisor.user']),
            ], 422);
        }

        // Buat employee record
        $employee = Employee::create([
            'user_id' => $user->id,
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'supervisor_id' => $request->supervisor_id,
            'join_date' => $request->join_date,
            'contract_status' => $request->contract_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Onboarding berhasil. {$candidate->name} telah menjadi karyawan.",
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'role']),
                'employee' => $employee->load(['department', 'position', 'supervisor.user']),
                'note' => 'Password default: "password". Informasikan kepada karyawan untuk segera mengganti password.',
            ],
        ], 201);
    }
}
