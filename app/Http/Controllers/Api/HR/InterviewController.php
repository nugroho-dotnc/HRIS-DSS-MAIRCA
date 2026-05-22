<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewScore;
use App\Models\InterviewSession;
use App\Models\RecruitmentCriteria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    /**
     * GET /api/hr/interviews
     * List semua sesi interview.
     */
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
            'data'    => $sessions,
        ]);
    }

    /**
     * POST /api/hr/interviews
     * Jadwalkan interview untuk kandidat yang lolos screening.
     * Mengubah status application menjadi 'interview_scheduled'.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'application_id'  => 'required|exists:applications,id',
            'interviewer_id'  => 'required|exists:users,id',
            'interview_date'  => 'required|date|after:now',
            'notes'           => 'nullable|string',
        ]);

        $application = Application::findOrFail($request->application_id);

        if ($application->status !== 'screening') {
            return response()->json([
                'success' => false,
                'message' => "Hanya lamaran berstatus 'screening' yang dapat dijadwalkan interview. Status saat ini: {$application->status}.",
            ], 422);
        }

        // Cek apakah sudah ada sesi interview untuk aplikasi ini
        $existingSession = InterviewSession::where('application_id', $request->application_id)->first();
        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi interview untuk lamaran ini sudah ada. Gunakan PUT untuk mengupdate.',
                'data'    => $existingSession,
            ], 422);
        }

        $session = InterviewSession::create([
            'application_id' => $request->application_id,
            'interviewer_id' => $request->interviewer_id,
            'interview_date' => $request->interview_date,
            'notes'          => $request->notes ?? '',
        ]);

        // Update status application
        $application->status = 'interview_scheduled';
        $application->save();

        return response()->json([
            'success' => true,
            'message' => 'Interview berhasil dijadwalkan.',
            'data'    => $session->load(['application.candidate', 'interviewer']),
        ], 201);
    }

    /**
     * GET /api/hr/interviews/{id}
     * Detail sesi interview beserta skor yang sudah diinput.
     */
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
            'data'    => $session,
        ]);
    }

    /**
     * PUT /api/hr/interviews/{id}
     * Update jadwal interview (tanggal, interviewer, notes).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $session = InterviewSession::findOrFail($id);

        $request->validate([
            'interviewer_id' => 'sometimes|exists:users,id',
            'interview_date' => 'sometimes|date',
            'notes'          => 'sometimes|nullable|string',
        ]);

        $session->fill($request->only(['interviewer_id', 'interview_date', 'notes']));
        $session->save();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal interview berhasil diupdate.',
            'data'    => $session->load(['application.candidate', 'interviewer']),
        ]);
    }

    /**
     * GET /api/hr/interviews/{id}/scores
     * Lihat skor MAIRCA yang sudah diinput untuk sesi ini.
     */
    public function getScores(string $id): JsonResponse
    {
        $session = InterviewSession::with([
            'scores.criteria',
            'application.vacancy.position.recruitment_criteria',
        ])->findOrFail($id);

        $criteria  = $session->application->vacancy->position->recruitment_criteria;
        $scores    = $session->scores->keyBy('criteria_id');
        $filled    = $criteria->count();
        $inputted  = $session->scores->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'session'              => $session,
                'total_criteria'       => $filled,
                'inputted_scores'      => $inputted,
                'is_complete'          => ($filled === $inputted),
                'scores_by_criteria'   => $criteria->map(function ($c) use ($scores) {
                    return [
                        'criteria_id'   => $c->id,
                        'criteria_name' => $c->name,
                        'weight'        => $c->weight,
                        'type'          => $c->type,
                        'data_type'     => $c->data_type,
                        'score'         => $scores->has($c->id) ? $scores[$c->id]->score : null,
                        'score_id'      => $scores->has($c->id) ? $scores[$c->id]->id : null,
                    ];
                }),
            ],
        ]);
    }

    /**
     * POST /api/hr/interviews/{id}/scores
     * Input skor MAIRCA per kriteria setelah interview selesai.
     * Body: array of { criteria_id, score }
     * Jika semua kriteria sudah terinput, status application → 'interview_done'.
     */
    public function storeScores(Request $request, string $id): JsonResponse
    {
        $session = InterviewSession::with(['application.vacancy.position.recruitment_criteria'])
            ->findOrFail($id);

        $request->validate([
            'scores'              => 'required|array|min:1',
            'scores.*.criteria_id' => 'required|exists:recruitment_criterias,id',
            'scores.*.score'       => 'required|numeric|min:0|max:100',
        ]);

        $application      = $session->application;
        $validCriteriaIds = $application->vacancy->position->recruitment_criteria->pluck('id')->toArray();

        $saved = [];

        foreach ($request->scores as $scoreData) {
            // Validasi: kriteria harus milik posisi vacancy ini
            if (! in_array($scoreData['criteria_id'], $validCriteriaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => "Kriteria ID {$scoreData['criteria_id']} tidak valid untuk posisi ini.",
                ], 422);
            }

            // Upsert: update jika sudah ada, insert jika belum
            $score = InterviewScore::updateOrCreate(
                [
                    'session_id'  => $id,
                    'criteria_id' => $scoreData['criteria_id'],
                ],
                [
                    'score' => $scoreData['score'],
                ]
            );

            $saved[] = $score;
        }

        // Cek apakah semua kriteria sudah terinput
        $totalCriteria  = count($validCriteriaIds);
        $inputtedScores = InterviewScore::where('session_id', $id)->count();

        if ($inputtedScores >= $totalCriteria) {
            // Semua skor sudah diinput — update status application ke interview_done
            $application->status = 'interview_done';
            $application->save();
        }

        return response()->json([
            'success'          => true,
            'message'          => 'Skor berhasil disimpan.',
            'scores_saved'     => count($saved),
            'total_criteria'   => $totalCriteria,
            'inputted_scores'  => $inputtedScores,
            'all_complete'     => $inputtedScores >= $totalCriteria,
            'application_status' => $application->fresh()->status,
        ]);
    }
}
