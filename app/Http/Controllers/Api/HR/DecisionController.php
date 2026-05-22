<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Employee;
use App\Models\RecruitmentResult;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DecisionController extends Controller
{
    /**
     * POST /api/hr/decisions/{applicationId}
     * Tetapkan keputusan final: 'hired' atau 'rejected'.
     * HR dapat override dari hasil ranking MAIRCA.
     */
    public function decide(Request $request, string $applicationId): JsonResponse
    {
        $application = Application::with(['candidate', 'vacancy.position', 'result'])
            ->findOrFail($applicationId);

        if ($application->status !== 'interview_done') {
            return response()->json([
                'success' => false,
                'message' => "Keputusan hanya bisa dibuat untuk lamaran berstatus 'interview_done'. Status saat ini: {$application->status}.",
            ], 422);
        }

        if (! $application->result) {
            return response()->json([
                'success' => false,
                'message' => 'Hasil kalkulasi MAIRCA belum ada untuk lamaran ini. Jalankan kalkulasi MAIRCA terlebih dahulu.',
            ], 422);
        }

        $request->validate([
            'decission' => ['required', Rule::in(['hired', 'rejected'])],
            'notes'     => 'nullable|string',
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
            'success'      => true,
            'message'      => $message,
            'data'         => [
                'application_id'   => $application->id,
                'candidate_name'   => $application->candidate->name,
                'decission'        => $request->decission,
                'mairca_ranking'   => $application->result->ranking,
                'mairca_score'     => $application->result->final_score,
            ],
        ]);
    }

    /**
     * POST /api/hr/onboarding/{applicationId}
     * Onboarding: buat data Employee dari kandidat yang sudah 'hired'.
     * Otomatis membuat user dengan role 'employee' (atau update jika email sudah ada).
     */
    public function onboarding(Request $request, string $applicationId): JsonResponse
    {
        $application = Application::with(['candidate', 'vacancy.position'])
            ->findOrFail($applicationId);

        if ($application->status !== 'hired') {
            return response()->json([
                'success' => false,
                'message' => "Onboarding hanya untuk kandidat dengan status 'hired'. Status saat ini: {$application->status}.",
            ], 422);
        }

        $candidate = $application->candidate;

        $request->validate([
            'department_id'   => 'required|exists:departments,id',
            'position_id'     => 'required|exists:positions,id',
            'supervisor_id'   => 'required|exists:employees,id',
            'join_date'       => 'required|date',
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
                'name'     => $candidate->name,
                'email'    => $candidate->email,
                'password' => Hash::make('password'), // Default password — harus diganti user
                'role'     => 'employee',
                'status'   => 'active',
            ]);
        }

        // Cek apakah employee record sudah ada
        $existingEmployee = Employee::where('user_id', $user->id)->first();
        if ($existingEmployee) {
            return response()->json([
                'success'  => false,
                'message'  => 'Data employee untuk user ini sudah ada.',
                'employee' => $existingEmployee->load(['user', 'department', 'position', 'supervisor.user']),
            ], 422);
        }

        // Buat employee record
        $employee = Employee::create([
            'user_id'         => $user->id,
            'department_id'   => $request->department_id,
            'position_id'     => $request->position_id,
            'supervisor_id'   => $request->supervisor_id,
            'join_date'       => $request->join_date,
            'contract_status' => $request->contract_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Onboarding berhasil. {$candidate->name} telah menjadi karyawan.",
            'data'    => [
                'user'     => $user->only(['id', 'name', 'email', 'role']),
                'employee' => $employee->load(['department', 'position', 'supervisor.user']),
                'note'     => 'Password default: "password". Informasikan kepada karyawan untuk segera mengganti password.',
            ],
        ], 201);
    }
}
