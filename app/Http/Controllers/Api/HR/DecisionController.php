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
use Illuminate\Http\Request;
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
        $application = Application::with(['candidate', 'vacancy.position.department', 'result'])
            ->findOrFail($applicationId);

        if ($application->status !== 'interview_done') {
            return response()->json([
                'success' => false,
                'message' => "Keputusan hanya bisa dibuat untuk lamaran berstatus 'interview_done'. Status saat ini: {$application->status}.",
                'data' => null,
            ], 422);
        }

        $request->validate([
            'decission' => ['required', \Illuminate\Validation\Rule::in(['hired', 'rejected'])],
            'notes' => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // update atau buat record baru jika belum ada
            if ($application->result) {
                $application->result->decission = $request->decission;
                $application->result->save();
            } else {
                RecruitmentResult::create([
                    "application_id" => $application->id,
                    "final_score" => 0.0,
                    "ranking" => 99,
                    "decission" => $request->decission
                ]);
            }

            // Update status application
            $application->status = $request->decission;
            $application->save();

            // OTOMATISASI ONBOARDING JIKA STATUS = HIRED
            if ($request->decission === 'hired') {
                $candidate = $application->candidate;
                $user = User::where("email", $candidate->email)->first();

                // 1. Daftarkan / perbarui user menjadi employee
                if ($user) {
                    if ($user->role === "candidate") {
                        $user->role = "employee";
                        $user->save();
                    }
                } else {
                    $user = User::create([
                        "name" => $candidate->name,
                        "email" => $candidate->email,
                        "password" => Hash::make("password"),
                        "role" => "employee",
                        "status" => "active"
                    ]);
                }

                // 2. Ambil departemen dan cari supervisor
                $deptId = $application->vacancy->position->department->id;

                $defaultSupervisor = Employee::where("department_id", $deptId)->whereHas("user", function ($query) {
                    $query->where("role", "supervisor");
                })->first();

                if (!$defaultSupervisor) {
                    $defaultSupervisor = Employee::whereHas("user", function ($query) {
                        $query->where("role", "supervisor");
                    })->first();
                }

                $supervisorId = $defaultSupervisor ? $defaultSupervisor->id : (Employee::first()?->id ?? 1);

                // 3. Masukkan ke tabel employees jika belum ada
                $exists = Employee::where("user_id", $user->id)->exists();
                if (!$exists) {
                    Employee::create([
                        "user_id" => $user->id,
                        "department_id" => $deptId,
                        "position_id" => $application->vacancy->position->id,
                        "supervisor_id" => $supervisorId,
                        "join_date" => now()->toDateString(),
                        "contract_status" => "probation",
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $application->load("result");

            $message = $request->decission === 'hired'
                ? 'Kandidat dinyatakan DITERIMA dan berhasil ditambahkan ke tabel employee.'
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses keputusan: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
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
