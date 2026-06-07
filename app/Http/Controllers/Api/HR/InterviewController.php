<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\RecruitmentCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InterviewController extends Controller
{
    #[OA\Get(
        path: '/hr/interviews',
        summary: 'Mendapatkan daftar sesi interview',
        description: 'List semua sesi interview dengan filter interviewer_id atau tanggal.',
        security: [['sanctum' => []]],
        tags: ['HR - Interviews'],
        parameters: [
            new OA\Parameter(name: 'interviewer_id', in: 'query', required: false, description: 'Filter berdasarkan ID interviewer', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'date', in: 'query', required: false, description: 'Filter berdasarkan tanggal', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, description: 'Jumlah data per halaman', schema: new OA\Schema(type: 'integer', default: 15))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar sesi interview berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/InterviewSession')),
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
        $query = InterviewSession::with([
            'application.candidate',
            'application.vacancy.position',
            'interviewer',
        ]);

        if ($request->has('interviewer_id')) {
            $query->where('interviewer_id', $request->interviewer_id);
        }

        if ($request->has('date')) {
            $query->whereDate('interview_date', $request->date);
        }

        $sessions = $query->orderBy('interview_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Daftar sesi interview berhasil diambil.',
            'data' => $sessions,
        ]);
    }

    #[OA\Post(
        path: '/hr/interviews',
        summary: 'Jadwalkan interview',
        description: "Jadwalkan interview untuk kandidat yang lolos screening. Mengubah status application menjadi 'interview_scheduled'.",
        security: [['sanctum' => []]],
        tags: ['HR - Interviews'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['application_id', 'interviewer_id', 'interview_date'],
                properties: [
                    new OA\Property(property: 'application_id', type: 'integer', example: 1),
                    new OA\Property(property: 'interviewer_id', type: 'integer', example: 2),
                    new OA\Property(property: 'interview_date', type: 'string', format: 'date-time', example: '2024-06-01 10:00:00'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Interview berhasil dijadwalkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Interview berhasil dijadwalkan.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/InterviewSession')
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
            'application_id' => 'required|exists:applications,id',
            'interviewer_id' => 'required|exists:users,id',
            'interview_date' => 'required|date|after:now',
            'notes' => 'nullable|string',
        ]);

        $application = Application::findOrFail($request->application_id);

        if ($application->status !== 'screening') {
            return response()->json([
                'success' => false,
                'message' => "Hanya lamaran berstatus 'screening' yang dapat dijadwalkan interview. Status saat ini: {$application->status}.",
                'data' => null,
            ], 422);
        }

        // Cek apakah sudah ada sesi interview untuk aplikasi ini
        $existingSession = InterviewSession::where('application_id', $request->application_id)->first();
        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi interview untuk lamaran ini sudah ada. Gunakan PUT untuk mengupdate.',
                'data' => $existingSession,
            ], 422);
        }

        $session = InterviewSession::create([
            'application_id' => $request->application_id,
            'interviewer_id' => $request->interviewer_id,
            'interview_date' => $request->interview_date,
            'notes' => $request->notes ?? '',
        ]);

        // Update status application
        $application->status = 'interview_scheduled';
        $application->save();

        //MAILER

        return response()->json([
            'success' => true,
            'message' => 'Interview berhasil dijadwalkan.',
            'data' => $session->load(['application.candidate', 'interviewer']),
        ], 201);
    }

    #[OA\Get(
        path: '/hr/interviews/{id}',
        summary: 'Melihat detail sesi interview',
        description: 'Detail sesi interview beserta skor yang sudah diinput.',
        security: [['sanctum' => []]],
        tags: ['HR - Interviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID sesi interview', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail sesi interview berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/InterviewSession')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Sesi interview tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $session = InterviewSession::with([
            'application.candidate',
            'application.vacancy.position.recruitment_criteria.likertScales',
            'interviewer',
            'scores.criteria',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail sesi interview berhasil diambil.',
            'data' => $session,
        ]);
    }

    #[OA\Put(
        path: '/hr/interviews/{id}',
        summary: 'Update jadwal interview',
        description: 'Update jadwal interview (tanggal, interviewer, notes).',
        security: [['sanctum' => []]],
        tags: ['HR - Interviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID sesi interview', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'interviewer_id', type: 'integer', example: 2),
                    new OA\Property(property: 'interview_date', type: 'string', format: 'date-time', example: '2024-06-01 10:00:00'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jadwal interview berhasil diupdate',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Jadwal interview berhasil diupdate.'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/InterviewSession')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Sesi interview tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        $session = InterviewSession::findOrFail($id);

        $request->validate([
            'interviewer_id' => 'sometimes|exists:users,id',
            'interview_date' => 'sometimes|date',
            'notes' => 'sometimes|nullable|string',
        ]);

        $session->fill($request->only(['interviewer_id', 'interview_date', 'notes']));
        $session->save();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal interview berhasil diupdate.',
            'data' => $session->load(['application.candidate', 'interviewer']),
        ]);
    }

    #[OA\Get(
        path: '/hr/interviews/{id}/scores',
        summary: 'Melihat skor interview',
        description: 'Lihat skor MAIRCA yang sudah diinput untuk sesi ini.',
        security: [['sanctum' => []]],
        tags: ['HR - Interviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID sesi interview', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skor interview berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'session', ref: '#/components/schemas/InterviewSession'),
                                new OA\Property(property: 'total_criteria', type: 'integer', example: 5),
                                new OA\Property(property: 'inputted_scores', type: 'integer', example: 5),
                                new OA\Property(property: 'is_complete', type: 'boolean', example: true),
                                new OA\Property(property: 'scores_by_criteria', type: 'array', items: new OA\Items(type: 'object'))
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Sesi interview tidak ditemukan'),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function getScores(string $id): JsonResponse
    {
        $session = InterviewSession::with([
            'scores.criteria',
            'application.vacancy.position.recruitment_criteria',
        ])->findOrFail($id);

        $criteria = $session->application->vacancy->position->recruitment_criteria;
        $scores = $session->scores->keyBy('criteria_id');
        $filled = $criteria->count();
        $inputted = $session->scores->count();

        return response()->json([
            'success' => true,
            'message' => 'Skor interview berhasil diambil.',
            'data' => [
                'session' => $session,
                'total_criteria' => $filled,
                'inputted_scores' => $inputted,
                'is_complete' => ($filled === $inputted),
                'scores_by_criteria' => $criteria->map(function ($c) use ($scores) {
                    return [
                        'criteria_id' => $c->id,
                        'criteria_name' => $c->name,
                        'weight' => $c->weight,
                        'type' => $c->type,
                        'data_type' => $c->data_type,
                        'score' => $scores->has($c->id) ? $scores[$c->id]->score : null,
                        'score_id' => $scores->has($c->id) ? $scores[$c->id]->id : null,
                    ];
                }),
            ],
        ]);
    }

    #[OA\Post(
        path: '/hr/interviews/{id}/scores',
        summary: 'Input skor interview',
        description: 'Input skor MAIRCA per kriteria setelah interview selesai. Jika semua kriteria sudah terinput, status application menjadi interview_done.',
        security: [['sanctum' => []]],
        tags: ['HR - Interviews'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID sesi interview', schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['scores'],
                properties: [
                    new OA\Property(
                        property: 'scores',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            required: ['criteria_id', 'score'],
                            properties: [
                                new OA\Property(property: 'criteria_id', type: 'integer', example: 1),
                                new OA\Property(property: 'score', type: 'number', format: 'float', example: 85.5)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Skor berhasil disimpan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Skor berhasil disimpan.'),
                        new OA\Property(property: 'scores_saved', type: 'integer', example: 1),
                        new OA\Property(property: 'total_criteria', type: 'integer', example: 5),
                        new OA\Property(property: 'inputted_scores', type: 'integer', example: 5),
                        new OA\Property(property: 'all_complete', type: 'boolean', example: true),
                        new OA\Property(property: 'application_status', type: 'string', example: 'interview_done')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Sesi interview tidak ditemukan'),
            new OA\Response(response: 422, description: 'Validasi gagal', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
            new OA\Response(response: 500, description: 'Server Error')
        ]
    )]
    public function storeScores(Request $request, string $id): JsonResponse
    {
        $session = InterviewSession::with(['application.vacancy.position.recruitment_criteria'])
            ->findOrFail($id);

        $request->validate([
            'scores' => 'required|array|min:1',
            'scores.*.criteria_id' => 'required|exists:recruitment_criterias,id',
            'scores.*.score' => 'required|numeric|min:0',
        ]);

        $application = $session->application;
        $criterias = $application->vacancy->position->recruitment_criteria->keyBy("id");
        $validCriteriaIds = $application->vacancy->position->recruitment_criteria->pluck('id')->toArray();

        $saved = [];

        foreach ($request->scores as $scoreData) {
            $criteriaId = $scoreData["criteria_id"];

            // Validasi: kriteria harus milik posisi vacancy ini
            if (!in_array($scoreData['criteria_id'], $validCriteriaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => "Kriteria ID {$scoreData['criteria_id']} tidak valid untuk posisi ini.",
                    'data' => null,
                ], 422);
            }

            $criteria = $criterias->get($criteriaId);
            $scoreVal = $scoreData["score"];

            // validasi untuk tipe data kuantitatif
            if ($criteria->data_type === "kuantitatif") {
                $nameLower = strtolower($criteria->name);
                $isIpk = str_contains($nameLower, "ipk");
                $isGaji = str_contains($nameLower, "gaji");

                if ($isIpk && $scoreVal > 4) {
                    return response()->json([
                        "success" => false,
                        "message" => "Kriteria '{$criteria->name}' (IPK) tidak boleh melebihi 4.00.",
                        "data" => null,
                    ], 422);
                }

                if (!$isIpk && !$isGaji && $scoreVal > 100) {
                    return response()->json([
                        "success" => false,
                        "message" => "Kriteria '{$criteria->name}' tidak boleh melebihi 100.",
                        "data" => null
                    ], 422);
                }
            }

            // Upsert: update jika sudah ada, insert jika belum
            $score = InterviewScore::updateOrCreate(
                [
                    'session_id' => $id,
                    'criteria_id' => $criteriaId,
                ],
                [
                    'score' => $scoreVal,
                ]
            );

            $saved[] = $score;
        }

        // Cek apakah semua kriteria sudah terinput
        $totalCriteria = count($validCriteriaIds);
        $inputtedScores = InterviewScore::where('session_id', $id)->count();

        if ($inputtedScores >= $totalCriteria) {
            // Semua skor sudah diinput — update status application ke interview_done
            $application->status = 'interview_done';
            $application->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Skor berhasil disimpan.',
            'data' => null,
            'scores_saved' => count($saved),
            'total_criteria' => $totalCriteria,
            'inputted_scores' => $inputtedScores,
            'all_complete' => $inputtedScores >= $totalCriteria,
            'application_status' => $application->fresh()->status,
        ]);
    }
}
