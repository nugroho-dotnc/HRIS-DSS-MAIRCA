<?php

namespace App\Http\Controllers\Api\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    /**
     * GET /api/vacancies
     * List semua vacancy yang berstatus 'open' — PUBLIC, tanpa autentikasi.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vacancies::with(['position.department'])
            ->where('status', 'open')
            ->where('deadline', '>=', now()->toDateString());

        if ($request->has('department_id')) {
            $query->whereHas('position', fn ($q) => $q->where('department_id', $request->department_id));
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $vacancies = $query->orderByDesc('created_at')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $vacancies,
        ]);
    }

    /**
     * GET /api/vacancies/{id}
     * Detail vacancy — PUBLIC, tanpa autentikasi.
     * Menampilkan deskripsi, requirements, departemen, dan deadline.
     */
    public function show(string $id): JsonResponse
    {
        $vacancy = Vacancies::with(['position.department'])
            ->where('status', 'open')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $vacancy->id,
                'title'       => $vacancy->title,
                'description' => $vacancy->description,
                'requirements'=> $vacancy->requirements,
                'position'    => $vacancy->position->position_name,
                'department'  => $vacancy->position->department->department_name,
                'deadline'    => $vacancy->deadline,
                'status'      => $vacancy->status,
                'posted_at'   => $vacancy->created_at->toDateString(),
            ],
        ]);
    }
}
