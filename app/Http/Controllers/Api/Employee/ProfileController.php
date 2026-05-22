<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * GET /api/employee/profile
     * Lihat data profil pribadi + kepegawaian employee yang sedang login.
     */
    public function show(Request $request): JsonResponse
    {
        $user     = $request->user();
        $employee = $user->employee()->with([
            'department',
            'position',
            'supervisor.user',
        ])->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'user'       => $user->only(['id', 'name', 'email', 'role', 'status']),
                'employment' => $employee,
            ],
        ]);
    }

    /**
     * PUT /api/employee/profile
     * Update data pribadi employee: nama, nomor telepon, alamat.
     * Data kepegawaian (posisi, departemen) TIDAK bisa diubah sendiri — domain HR.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diupdate.',
            'data'    => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    /**
     * GET /api/employee/employment
     * Lihat data kepegawaian secara terpisah (read-only).
     */
    public function employment(Request $request): JsonResponse
    {
        $user     = $request->user();
        $employee = $user->employee()->with([
            'department',
            'position.recruitment_criteria',
            'supervisor.user',
            'subordinates',
        ])->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data kepegawaian belum tersedia.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'employee_id'     => $employee->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'department'      => $employee->department->department_name,
                'position'        => $employee->position->position_name,
                'supervisor'      => $employee->supervisor ? $employee->supervisor->user->name : null,
                'join_date'       => $employee->join_date,
                'contract_status' => $employee->contract_status,
                'note'            => 'Data kepegawaian bersifat read-only. Hubungi HR untuk perubahan.',
            ],
        ]);
    }
}
