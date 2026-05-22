<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LikertScale;
use App\Models\RecruitmentCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecruitmentCriteriaController extends Controller
{
    /**
     * GET /api/admin/criteria
     * List semua kriteria MAIRCA, bisa filter per position_id.
     */
    public function index(Request $request): JsonResponse
    {
        $query = RecruitmentCriteria::with(['position.department', 'likertScales']);

        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        $criteria = $query->orderBy('position_id')->orderBy('name')->get();

        // Validasi total bobot per posisi
        $grouped = $criteria->groupBy('position_id')->map(function ($group) {
            return $group->sum('weight');
        });

        return response()->json([
            'success'      => true,
            'data'         => $criteria,
            'weight_check' => $grouped, // Tampilkan total bobot per posisi untuk validasi
        ]);
    }

    /**
     * POST /api/admin/criteria
     * Tambah kriteria baru untuk suatu posisi.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'name'        => 'required|string|max:255',
            'weight'      => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'type'        => ['required', Rule::in(['benefit', 'cost'])],
            'data_type'   => ['required', Rule::in(['kualitatif', 'kuantitatif'])],
        ]);

        // Validasi: total bobot per posisi tidak boleh melebihi 100%
        $existingWeight = RecruitmentCriteria::where('position_id', $request->position_id)
            ->sum('weight');

        if (($existingWeight + $request->weight) > 100) {
            return response()->json([
                'success'           => false,
                'message'           => 'Total bobot kriteria untuk posisi ini akan melebihi 100%. Sisa bobot yang tersedia: ' . (100 - $existingWeight) . '%.',
                'existing_weight'   => $existingWeight,
                'available_weight'  => 100 - $existingWeight,
            ], 422);
        }

        $criteria = RecruitmentCriteria::create($request->only([
            'position_id', 'name', 'weight', 'description', 'type', 'data_type',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Kriteria MAIRCA berhasil ditambahkan.',
            'data'    => $criteria->load(['position', 'likertScales']),
        ], 201);
    }

    /**
     * GET /api/admin/criteria/{id}
     */
    public function show(string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::with(['position.department', 'likertScales'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $criteria,
        ]);
    }

    /**
     * PUT /api/admin/criteria/{id}
     * Update kriteria + bobot. Validasi total bobot = 100%.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);

        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'weight'      => 'sometimes|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'type'        => ['sometimes', Rule::in(['benefit', 'cost'])],
            'data_type'   => ['sometimes', Rule::in(['kualitatif', 'kuantitatif'])],
        ]);

        if ($request->has('weight')) {
            // Cek total bobot (tidak termasuk kriteria ini sendiri)
            $otherWeight = RecruitmentCriteria::where('position_id', $criteria->position_id)
                ->where('id', '!=', $id)
                ->sum('weight');

            if (($otherWeight + $request->weight) > 100) {
                return response()->json([
                    'success'          => false,
                    'message'          => 'Total bobot kriteria akan melebihi 100%. Bobot dari kriteria lain: ' . $otherWeight . '%.',
                    'available_weight' => 100 - $otherWeight,
                ], 422);
            }
        }

        $criteria->fill($request->only(['name', 'weight', 'description', 'type', 'data_type']));
        $criteria->save();

        return response()->json([
            'success' => true,
            'message' => 'Kriteria berhasil diupdate.',
            'data'    => $criteria->load(['position', 'likertScales']),
        ]);
    }

    /**
     * DELETE /api/admin/criteria/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);
        $criteria->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kriteria berhasil dihapus.',
        ]);
    }

    // ─── Likert Scale Management ───────────────────────────────────────────────

    /**
     * GET /api/admin/criteria/{id}/likert
     * List skala Likert untuk kriteria ini.
     */
    public function likertIndex(string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);

        if ($criteria->data_type !== 'kualitatif') {
            return response()->json([
                'success' => false,
                'message' => 'Kriteria ini bertipe kuantitatif, tidak memiliki skala Likert.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => $criteria->likertScales()->orderBy('value')->get(),
        ]);
    }

    /**
     * POST /api/admin/criteria/{id}/likert
     * Tambah opsi skala Likert ke kriteria kualitatif.
     */
    public function likertStore(Request $request, string $id): JsonResponse
    {
        $criteria = RecruitmentCriteria::findOrFail($id);

        if ($criteria->data_type !== 'kualitatif') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya kriteria kualitatif yang dapat memiliki skala Likert.',
            ], 422);
        }

        $request->validate([
            'label' => 'required|string|max:255',
            'value' => 'required|numeric',
        ]);

        $scale = LikertScale::create([
            'recruitment_criterias_id' => $id,
            'label'                    => $request->label,
            'value'                    => $request->value,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Skala Likert berhasil ditambahkan.',
            'data'    => $scale,
        ], 201);
    }

    /**
     * DELETE /api/admin/criteria/{id}/likert/{scaleId}
     * Hapus opsi skala Likert.
     */
    public function likertDestroy(string $id, string $scaleId): JsonResponse
    {
        $scale = LikertScale::where('recruitment_criterias_id', $id)
            ->where('id', $scaleId)
            ->firstOrFail();

        $scale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skala Likert berhasil dihapus.',
        ]);
    }
}
