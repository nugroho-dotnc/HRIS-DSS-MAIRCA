<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    /**
     * GET /api/admin/positions
     */
    public function index(Request $request): JsonResponse
    {
        $query = Position::with('department');

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $positions = $query->orderBy('position_name')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data'    => $positions,
        ]);
    }

    /**
     * POST /api/admin/positions
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_name' => 'required|string|max:255',
            'is_active'     => 'sometimes|boolean',
        ]);

        // Cek unique per departemen
        $exists = Position::where('department_id', $request->department_id)
            ->where('position_name', $request->position_name)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Posisi dengan nama yang sama sudah ada di departemen ini.',
            ], 422);
        }

        $position = Position::create([
            'department_id' => $request->department_id,
            'position_name' => $request->position_name,
            'is_active'     => $request->get('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Posisi berhasil dibuat.',
            'data'    => $position->load('department'),
        ], 201);
    }

    /**
     * GET /api/admin/positions/{id}
     */
    public function show(string $id): JsonResponse
    {
        $position = Position::with(['department', 'recruitment_criteria.likertScales'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $position,
        ]);
    }

    /**
     * PUT /api/admin/positions/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $position = Position::findOrFail($id);

        $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'position_name' => 'sometimes|string|max:255',
            'is_active'     => 'sometimes|boolean',
        ]);

        $position->fill($request->only(['department_id', 'position_name', 'is_active']));
        $position->save();

        return response()->json([
            'success' => true,
            'message' => 'Posisi berhasil diupdate.',
            'data'    => $position->load('department'),
        ]);
    }

    /**
     * DELETE /api/admin/positions/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $position = Position::findOrFail($id);

        if ($position->vacancies()->where('status', 'open')->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Posisi tidak dapat dihapus karena masih ada lowongan aktif.',
            ], 422);
        }

        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'Posisi berhasil dihapus.',
        ]);
    }
}
