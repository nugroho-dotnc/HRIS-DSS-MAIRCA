<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/admin/users',
        summary: 'Mendapatkan daftar user',
        description: 'List semua user dengan filter role dan status opsional.',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        parameters: [
            new OA\Parameter(name: 'role', in: 'query', required: false, description: 'Filter berdasarkan role', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter berdasarkan status', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', required: false, description: 'Pencarian berdasarkan nama atau email', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar user berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
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
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar user berhasil diambil.',
            'data' => $users,
        ]);
    }

    #[OA\Post(
        path: '/admin/users',
        summary: 'Membuat user baru',
        description: 'Buat user baru (HR, Supervisor, Employee). Candidate dibuat via register.',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Fulan'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'fulan@hris.local'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['hr', 'supervisor', 'employee'], example: 'hr'),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User berhasil dibuat.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User')
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['hr', 'supervisor', 'employee'])],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->get('status', 'active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat.',
            'data' => $user,
        ], 201);
    }

    #[OA\Get(
        path: '/admin/users/{id}',
        summary: 'Melihat detail user',
        description: 'Detail user beserta data employee jika ada.',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID user', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data user berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $user = User::with(['employee.department', 'employee.position'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail user berhasil diambil.',
            'data' => $user,
        ]);
    }

    #[OA\Put(
        path: '/admin/users/{id}',
        summary: 'Update user',
        description: 'Update data user (nama, email, password).',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID user', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Fulan Update'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'fulan_update@hris.local'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('name'))
            $user->name = $request->name;
        if ($request->has('email'))
            $user->email = $request->email;
        if ($request->has('password'))
            $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate.',
            'data' => $user,
        ]);
    }

    #[OA\Patch(
        path: '/admin/users/{id}/role',
        summary: 'Update role user',
        description: 'Ubah role user — hanya admin yang bisa.',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID user', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['role'],
                properties: [
                    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'hr', 'supervisor', 'employee', 'candidate'], example: 'supervisor')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Role user berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: "Role user berhasil diubah dari 'hr' menjadi 'supervisor'."),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function updateRole(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => ['required', Rule::in(['admin', 'hr', 'supervisor', 'employee', 'candidate'])],
        ]);

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => "Role user berhasil diubah dari '{$oldRole}' menjadi '{$request->role}'.",
            'data' => $user,
        ]);
    }

    #[OA\Patch(
        path: '/admin/users/{id}/status',
        summary: 'Update status user',
        description: 'Aktifkan atau nonaktifkan akun user.',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID user', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'inactive')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status user berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Akun user berhasil dinonaktifkan.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/User')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $user->status = $request->status;
        $user->save();

        $label = $request->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'success' => true,
            'message' => "Akun user berhasil {$label}.",
            'data' => $user,
        ]);
    }

    #[OA\Delete(
        path: '/admin/users/{id}',
        summary: 'Hapus user',
        description: 'Hapus user. Tidak bisa menghapus akun sendiri.',
        security: [['sanctum' => []]],
        tags: ['Admin - Users'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID user', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'User berhasil dihapus.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'User tidak ditemukan'),
            new OA\Response(response: 422, description: 'Tidak bisa menghapus akun sendiri', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Jangan hapus diri sendiri
        if ($user->id === request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun sendiri.',
                'data' => null,
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
            'data' => null,
        ]);
    }
}
