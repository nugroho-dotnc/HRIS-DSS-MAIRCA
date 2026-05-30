<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DepartmentController extends Controller
{
    #[OA\Get(
        path: '/departments',
        summary: 'Mendapatkan daftar departemen (publik)',
        description: 'Mengambil semua departemen yang aktif. Endpoint ini bersifat publik dan tidak memerlukan autentikasi.',
        tags: ['Public - Departments'],
        parameters: [
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan status aktif (default: true)',
                schema: new OA\Schema(type: 'boolean')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar departemen berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Department')
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Department::withCount('positions');

        // Default hanya tampilkan yang aktif, kecuali ada filter eksplisit
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->where('is_active', true);
        }

        $departments = $query->orderBy('department_name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar departemen berhasil diambil.',
            'data'    => $departments,
        ]);
    }

    #[OA\Get(
        path: '/departments/{id}',
        summary: 'Melihat detail departemen (publik)',
        description: 'Mengambil data detail satu departemen beserta daftar posisi aktif yang dimilikinya. Endpoint ini bersifat publik.',
        tags: ['Public - Departments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID departemen',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail departemen berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Department')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Departemen tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $department = Department::with([
            'positions' => fn ($q) => $q->where('is_active', true)->orderBy('position_name'),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail departemen berhasil diambil.',
            'data'    => $department,
        ]);
    }
}
