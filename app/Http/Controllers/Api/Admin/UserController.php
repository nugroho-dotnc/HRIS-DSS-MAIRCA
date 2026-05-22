<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/admin/users
     * List semua user dengan filter role opsional.
     */
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
            'data'    => $users,
        ]);
    }

    /**
     * POST /api/admin/users
     * Buat user baru (HR, Supervisor, Employee). Candidate dibuat via register.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => ['required', Rule::in(['hr', 'supervisor', 'employee'])],
            'status'   => ['sometimes', Rule::in(['active', 'inactive'])],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => $request->get('status', 'active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat.',
            'data'    => $user,
        ], 201);
    }

    /**
     * GET /api/admin/users/{id}
     * Detail user beserta data employee jika ada.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['employee.department', 'employee.position'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    /**
     * PUT /api/admin/users/{id}
     * Update data user (nama, email).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'sometimes|string|min:8',
        ]);

        if ($request->has('name'))     $user->name = $request->name;
        if ($request->has('email'))    $user->email = $request->email;
        if ($request->has('password')) $user->password = Hash::make($request->password);

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate.',
            'data'    => $user,
        ]);
    }

    /**
     * PATCH /api/admin/users/{id}/role
     * Ubah role user — hanya admin yang bisa.
     */
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
            'success'  => true,
            'message'  => "Role user berhasil diubah dari '{$oldRole}' menjadi '{$request->role}'.",
            'data'     => $user,
        ]);
    }

    /**
     * PATCH /api/admin/users/{id}/status
     * Aktifkan atau nonaktifkan akun user.
     */
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
            'data'    => $user,
        ]);
    }

    /**
     * DELETE /api/admin/users/{id}
     * Hapus user.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // Jangan hapus diri sendiri
        if ($user->id === request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus akun sendiri.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus.',
        ]);
    }
}
