<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DepartmentController extends Controller
{
    #[OA\Get(
        path: '/admin/departments',
        summary: 'Mendapatkan daftar departemen',
        description: 'Mengambil semua data departemen beserta jumlah posisinya.',
        security: [['sanctum' => []]],
        tags: ['Admin - Departments'],
        parameters: [
            new OA\Parameter(
                name: 'is_active',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan status aktif (true/false)',
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
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Department::withCount('positions');

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $departments = $query->orderBy('department_name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar departemen berhasil diambil.',
            'data' => $departments,
        ]);
    }

    #[OA\Post(
        path: '/admin/departments',
        summary: 'Membuat departemen baru',
        description: 'Menambahkan departemen baru ke dalam sistem.',
        security: [['sanctum' => []]],
        tags: ['Admin - Departments'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['department_name'],
                properties: [
                    new OA\Property(property: 'department_name', type: 'string', example: 'Teknologi Informasi'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Departemen berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Departemen berhasil dibuat.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Department')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
            'is_active' => 'sometimes|boolean',
        ]);

        $department = Department::create([
            'department_name' => $request->department_name,
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil dibuat.',
            'data' => $department,
        ], 201);
    }

    #[OA\Get(
        path: '/admin/departments/{id}',
        summary: 'Melihat detail departemen',
        description: 'Mengambil data detail departemen berdasarkan ID beserta posisinya.',
        security: [['sanctum' => []]],
        tags: ['Admin - Departments'],
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
                description: 'Data departemen berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Department')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Departemen tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $department = Department::with(['positions', 'employees.user'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail departemen berhasil diambil.',
            'data' => $department,
        ]);
    }

    #[OA\Put(
        path: '/admin/departments/{id}',
        summary: 'Update departemen',
        description: 'Memperbarui data departemen berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Departments'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID departemen',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'department_name', type: 'string', example: 'Teknologi Informasi Update'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: false)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Departemen berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Departemen berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Department')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Departemen tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'department_name' => 'sometimes|string|max:255|unique:departments,department_name,' . $department->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $department->fill($request->only(['department_name', 'is_active']));
        $department->save();

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil diupdate.',
            'data' => $department,
        ]);
    }

    #[OA\Delete(
        path: '/admin/departments/{id}',
        summary: 'Hapus departemen',
        description: 'Menghapus departemen berdasarkan ID.',
        security: [['sanctum' => []]],
        tags: ['Admin - Departments'],
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
                description: 'Departemen berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Departemen berhasil dihapus.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Departemen tidak ditemukan'),
            new OA\Response(response: 422, description: 'Departemen masih memiliki posisi', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $department = Department::findOrFail($id);

        if ($department->positions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Departemen tidak dapat dihapus karena masih memiliki posisi terkait.',
                'data' => null,
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => 'Departemen berhasil dihapus.',
            'data' => null,
        ]);
    }
}
