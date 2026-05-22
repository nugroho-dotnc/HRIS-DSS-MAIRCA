<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * GET /api/admin/departments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::withCount('positions');

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $departments = $query->orderBy('department_name')->get();

        return response()->json([
            'success' => true,
            'data'    => $departments,
        ]);
    }

    /**
     * POST /api/admin/departments
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
            'is_active'       => 'sometimes|boolean',
        ]);

        $department = Department::create([
            'department_name' => $request->department_name,
            'is_active'       => $request->get('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil dibuat.',
            'data'    => $department,
        ], 201);
    }

    /**
     * GET /api/admin/departments/{id}
     */
    public function show(string $id): JsonResponse
    {
        $department = Department::with(['positions', 'employees.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $department,
        ]);
    }

    /**
     * PUT /api/admin/departments/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'department_name' => 'sometimes|string|max:255|unique:departments,department_name,' . $department->id,
            'is_active'       => 'sometimes|boolean',
        ]);

        $department->fill($request->only(['department_name', 'is_active']));
        $department->save();

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil diupdate.',
            'data'    => $department,
        ]);
    }

    /**
     * DELETE /api/admin/departments/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        if ($department->positions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Departemen tidak dapat dihapus karena masih memiliki posisi terkait.',
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil dihapus.',
        ]);
    }
}
