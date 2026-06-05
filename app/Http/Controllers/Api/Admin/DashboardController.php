<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use App\Models\RecruitmentCriteria;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/admin/dashboard',
        summary: 'Mendapatkan data ringkasan admin dashboard',
        description: 'Mengambil data ringkasan untuk halaman dashboard admin (sama dengan versi web).',
        security: [['sanctum' => []]],
        tags: ['Admin - Dashboard'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data dashboard berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data dashboard berhasil diambil.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'stats', type: 'object'),
                                new OA\Property(property: 'role_distribution', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'master_data_health', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'latest_users', type: 'array', items: new OA\Items(type: 'object')),
                                new OA\Property(property: 'positions_criteria', type: 'array', items: new OA\Items(type: 'object'))
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function index(): JsonResponse
    {
        // 1. Stats Overview
        $stats = [
            'total_users' => User::count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_departments' => Department::count(),
            'active_departments' => Department::where('is_active', true)->count(),
            'total_positions' => Position::count(),
            'active_positions' => Position::where('is_active', true)->count(),
            'total_criteria' => RecruitmentCriteria::count(),
            'positions_without_criteria' => Position::doesntHave('recruitment_criteria')->count(),
        ];

        // 2. Role Distribution
        $labels = [
            'admin' => ['label' => 'Admin', 'color' => 'bg-blue-500'],
            'hr' => ['label' => 'HR', 'color' => 'bg-emerald-500'],
            'supervisor' => ['label' => 'Supervisor', 'color' => 'bg-amber-500'],
            'employee' => ['label' => 'Employee', 'color' => 'bg-indigo-500'],
            'candidate' => ['label' => 'Candidate', 'color' => 'bg-zinc-500'],
        ];

        $counts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $max = max(1, (int) $counts->max());

        $roleDistribution = collect($labels)->map(function ($meta, $role) use ($counts, $max) {
            $value = (int) ($counts[$role] ?? 0);
            return [
                'role' => $role,
                'label' => $meta['label'],
                'color' => $meta['color'],
                'value' => $value,
                'percent' => $value > 0 ? max(8, round(($value / $max) * 100)) : 0,
            ];
        })->values()->all();

        // 3. Master Data Health
        $masterDataHealth = [
            [
                'label' => 'Active Departments',
                'value' => $stats['active_departments'],
                'total' => $stats['total_departments'],
                'icon' => 'building-office-2',
                'color' => 'green',
            ],
            [
                'label' => 'Inactive Departments',
                'value' => $stats['total_departments'] - $stats['active_departments'],
                'total' => $stats['total_departments'],
                'icon' => 'archive-box-x-mark',
                'color' => 'red',
            ],
            [
                'label' => 'Active Positions',
                'value' => $stats['active_positions'],
                'total' => $stats['total_positions'],
                'icon' => 'briefcase',
                'color' => 'green',
            ],
            [
                'label' => 'Positions Without Criteria',
                'value' => $stats['positions_without_criteria'],
                'total' => $stats['total_positions'],
                'icon' => 'exclamation-triangle',
                'color' => 'amber',
            ],
        ];

        // 4. Latest Users
        $latestUsers = User::select(['id', 'name', 'email', 'role', 'is_active', 'created_at'])
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at,
                ];
            });

        // 5. Positions & Criteria
        $positionsCriteria = Position::with('department')
            ->withCount('recruitment_criteria')
            ->withSum('recruitment_criteria', 'weight')
            ->orderBy('recruitment_criteria_count')
            ->limit(6)
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'position_name' => $position->position_name,
                    'is_active' => $position->is_active,
                    'department_name' => $position->department->department_name ?? '-',
                    'total_criteria' => $position->recruitment_criteria_count,
                    'total_weight' => (float) ($position->recruitment_criteria_sum_weight ?? 0),
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil.',
            'data' => [
                'stats' => $stats,
                'role_distribution' => $roleDistribution,
                'master_data_health' => $masterDataHealth,
                'latest_users' => $latestUsers,
                'positions_criteria' => $positionsCriteria,
            ]
        ]);
    }
}
