<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VacancyController extends Controller
{
    /**
     * GET /api/hr/vacancies
     * List semua vacancy (termasuk yang dibuat HR lain).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vacancies::with(['position.department', 'hr'])
            ->withCount('applications');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        if ($request->has('my_vacancies') && $request->my_vacancies) {
            $query->where('hr_id', $request->user()->id);
        }

        $vacancies = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $vacancies,
        ]);
    }

    /**
     * POST /api/hr/vacancies
     * Buat lowongan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'position_id'  => 'required|exists:positions,id',
            'title'        => 'required|string|max:255',
            'description'  => 'required|string',
            'requirements' => 'required|string',
            'deadline'     => 'required|date|after:today',
        ]);

        $vacancy = Vacancies::create([
            'hr_id'        => $request->user()->id,
            'position_id'  => $request->position_id,
            'title'        => $request->title,
            'description'  => $request->description,
            'requirements' => $request->requirements,
            'deadline'     => $request->deadline,
            'status'       => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dibuat.',
            'data'    => $vacancy->load(['position.department', 'hr']),
        ], 201);
    }

    /**
     * GET /api/hr/vacancies/{id}
     */
    public function show(string $id): JsonResponse
    {
        $vacancy = Vacancies::with([
            'position.department',
            'position.recruitment_criteria.likertScales',
            'hr',
            'applications.candidate',
        ])->withCount('applications')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $vacancy,
        ]);
    }

    /**
     * PUT /api/hr/vacancies/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $vacancy = Vacancies::findOrFail($id);

        if ($vacancy->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan yang sudah ditutup tidak dapat diubah.',
            ], 422);
        }

        $request->validate([
            'title'        => 'sometimes|string|max:255',
            'description'  => 'sometimes|string',
            'requirements' => 'sometimes|string',
            'deadline'     => 'sometimes|date|after:today',
            'position_id'  => 'sometimes|exists:positions,id',
        ]);

        $vacancy->fill($request->only(['title', 'description', 'requirements', 'deadline', 'position_id']));
        $vacancy->save();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil diupdate.',
            'data'    => $vacancy->load(['position.department']),
        ]);
    }

    /**
     * PATCH /api/hr/vacancies/{id}/close
     * Tutup lowongan — posisi sudah terisi.
     */
    public function close(string $id): JsonResponse
    {
        $vacancy = Vacancies::findOrFail($id);

        if ($vacancy->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan sudah dalam status closed.',
            ], 422);
        }

        $vacancy->status = 'closed';
        $vacancy->save();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil ditutup.',
            'data'    => $vacancy,
        ]);
    }

    /**
     * DELETE /api/hr/vacancies/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $vacancy = Vacancies::findOrFail($id);

        if ($vacancy->applications()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Lowongan tidak dapat dihapus karena sudah memiliki lamaran masuk.',
            ], 422);
        }

        $vacancy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lowongan berhasil dihapus.',
        ]);
    }
}
