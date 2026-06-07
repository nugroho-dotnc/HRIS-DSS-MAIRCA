<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class EmployeeController extends Controller
{
    #[OA\Get(
        path: '/hr/employees',
        summary: 'Mendapatkan daftar karyawan',
        description: 'List semua karyawan aktif. HR bisa melihat dan mengelola semua.',
        security: [['sanctum' => []]],
        tags: ['HR - Employees'],
        parameters: [
            new OA\Parameter(name: 'department_id', in: 'query', required: false, description: 'Filter berdasarkan departemen', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'position_id', in: 'query', required: false, description: 'Filter berdasarkan posisi', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'contract_status', in: 'query', required: false, description: 'Filter berdasarkan status kontrak', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Pencarian berdasarkan nama atau email', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar karyawan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Employee')),
                                new OA\Property(property: 'total', type: 'integer', example: 50)
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
        if ($employees->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Data tidak ditemukan.',
                'data'    => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar karyawan berhasil diambil.',
            'data' => $employees,
        ]);
    }

    #[OA\Get(
        path: '/hr/employees/{id}',
        summary: 'Melihat detail karyawan',
        description: 'Detail data kepegawaian karyawan beserta atasan dan bawahan.',
        security: [['sanctum' => []]],
        tags: ['HR - Employees'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID karyawan (employee.id)', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail karyawan berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Employee')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Karyawan tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
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
            'message' => 'Detail karyawan berhasil diambil.',
            'data' => $employee,
        ]);
    }

    #[OA\Put(
        path: '/hr/employees/{id}',
        summary: 'Update data karyawan',
        description: 'Update data kepegawaian karyawan (posisi, departemen, kontrak, supervisor).',
        security: [['sanctum' => []]],
        tags: ['HR - Employees'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID karyawan', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'department_id', type: 'integer', example: 2),
                    new OA\Property(property: 'position_id', type: 'integer', example: 2),
                    new OA\Property(property: 'supervisor_id', type: 'integer', example: 3),
                    new OA\Property(property: 'join_date', type: 'string', format: 'date', example: '2023-05-01'),
                    new OA\Property(property: 'contract_status', type: 'string', enum: ['permanent', 'contract', 'probation'], example: 'permanent')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data karyawan berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Data kepegawaian berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Employee')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Karyawan tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal (misal supervisor adalah diri sendiri)', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'position_id' => 'sometimes|exists:positions,id',
            'supervisor_id' => [
                'sometimes',
                'exists:employees,id',
                // Supervisor tidak bisa menunjuk diri sendiri
                Rule::notIn([$employee->id]),
            ],
            'join_date' => 'sometimes|date',
            'contract_status' => ['sometimes', Rule::in(['permanent', 'contract', 'probation'])],
        ]);

        $employee->fill($request->only([
            'department_id',
            'position_id',
            'supervisor_id',
            'join_date',
            'contract_status',
        ]));
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Data kepegawaian berhasil diupdate.',
            'data' => $employee->load(['user', 'department', 'position', 'supervisor.user']),
        ]);
    }
}
