<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * GET /api/hr/employees
     * List semua karyawan aktif — HR bisa melihat dan mengelola semua.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['user', 'department', 'position', 'supervisor.user']);

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->has('contract_status')) {
            $query->where('contract_status', $request->contract_status);
        }

        if ($request->has('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $employees = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $employees,
        ]);
    }

    /**
     * GET /api/hr/employees/{id}
     * Detail data kepegawaian karyawan.
     */
    public function show(string $id): JsonResponse
    {
        $employee = Employee::with([
            'user',
            'department',
            'position',
            'supervisor.user',
            'subordinates.user',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $employee,
        ]);
    }

    /**
     * PUT /api/hr/employees/{id}
     * Update data kepegawaian karyawan (posisi, departemen, kontrak, supervisor).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'department_id'   => 'sometimes|exists:departments,id',
            'position_id'     => 'sometimes|exists:positions,id',
            'supervisor_id'   => [
                'sometimes',
                'exists:employees,id',
                // Supervisor tidak bisa menunjuk diri sendiri
                Rule::notIn([$employee->id]),
            ],
            'join_date'       => 'sometimes|date',
            'contract_status' => ['sometimes', Rule::in(['permanent', 'contract', 'probation'])],
        ]);

        $employee->fill($request->only([
            'department_id', 'position_id', 'supervisor_id', 'join_date', 'contract_status',
        ]));
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kepegawaian berhasil diupdate.',
            'data'    => $employee->load(['user', 'department', 'position', 'supervisor.user']),
        ]);
    }
}
