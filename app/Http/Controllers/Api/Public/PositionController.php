<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PositionController extends Controller
{
    #[OA\Get(
        path: '/positions',
        summary: 'Mendapatkan daftar posisi (publik)',
        description: 'Mengambil semua posisi yang aktif beserta departemennya. Endpoint ini bersifat publik dan tidak memerlukan autentikasi.',
        tags: ['Public - Positions'],
        parameters: [
            new OA\Parameter(
                name: 'department_id',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan ID departemen',
                schema: new OA\Schema(type: 'integer')
            ),
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
                description: 'Daftar posisi berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Position')
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Position::with('department');

        // Default hanya tampilkan yang aktif, kecuali ada filter eksplisit
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        } else {
            $query->where('is_active', true);
        }

        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $positions = $query->orderBy('position_name')->get();

        return response()->json([
            'success' => true,
            'data'    => $positions,
        ]);
    }

    #[OA\Get(
        path: '/positions/{id}',
        summary: 'Melihat detail posisi (publik)',
        description: 'Mengambil data detail satu posisi beserta departemennya. Endpoint ini bersifat publik.',
        tags: ['Public - Positions'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID posisi',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail posisi berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Position')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Posisi tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $position = Position::with('department')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $position,
        ]);
    }
}
