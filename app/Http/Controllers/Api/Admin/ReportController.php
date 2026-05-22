<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * GET /api/admin/reports/recruitment
     * Laporan rekap rekrutmen lintas divisi — read only.
     */
    public function recruitment(Request $request): JsonResponse
    {
        // Summary keseluruhan
        $summary = [
            'total_vacancies'      => Vacancies::count(),
            'open_vacancies'       => Vacancies::where('status', 'open')->count(),
            'closed_vacancies'     => Vacancies::where('status', 'closed')->count(),
            'total_applications'   => Application::count(),
            'applications_by_status' => Application::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status'),
        ];

        // Rekap per departemen
        $byDepartment = DB::table('vacancies')
            ->join('positions', 'vacancies.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->leftJoin('applications', 'vacancies.id', '=', 'applications.vacancy_id')
            ->select(
                'departments.department_name',
                DB::raw('COUNT(DISTINCT vacancies.id) as total_vacancies'),
                DB::raw('COUNT(applications.id) as total_applications'),
                DB::raw('SUM(CASE WHEN applications.status = "hired" THEN 1 ELSE 0 END) as total_hired'),
                DB::raw('SUM(CASE WHEN applications.status = "rejected" THEN 1 ELSE 0 END) as total_rejected')
            )
            ->groupBy('departments.id', 'departments.department_name')
            ->orderBy('departments.department_name')
            ->get();

        // Top vacancies berdasarkan jumlah pelamar
        $topVacancies = Vacancies::withCount('applications')
            ->with(['position.department'])
            ->orderByDesc('applications_count')
            ->limit(10)
            ->get()
            ->map(fn ($v) => [
                'id'                  => $v->id,
                'title'               => $v->title,
                'position'            => $v->position->position_name ?? '-',
                'department'          => $v->position->department->department_name ?? '-',
                'status'              => $v->status,
                'deadline'            => $v->deadline,
                'total_applications'  => $v->applications_count,
            ]);

        // Rekap hasil MAIRCA
        $maircaSummary = DB::table('recruitment_results')
            ->select(
                'decission',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(final_score) as avg_score')
            )
            ->groupBy('decission')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'summary'        => $summary,
                'by_department'  => $byDepartment,
                'top_vacancies'  => $topVacancies,
                'mairca_summary' => $maircaSummary,
                'generated_at'   => now()->toDateTimeString(),
            ],
        ]);
    }
}
