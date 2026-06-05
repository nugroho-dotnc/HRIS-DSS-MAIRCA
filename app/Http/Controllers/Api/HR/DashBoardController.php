<?php

namespace App\Http\Controllers\Api\HR;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InterviewSession;
use App\Models\Vacancies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DashBoardController extends Controller
{
    #[OA\Get(
        path: "hr/dashboard",
        summary: "Mendapatkan data ringkasan dashboad HR",
        description: "Mengambil data statistik utama, recruitment pipeline, interview mendatang, lowongan yang akan segera tutup, dan lamaran masuk terbaru.",
        security: [["sanctum" => []]],
        tags: ["HR - Dashboard"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Data dashboard HR berhasil diambil",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Data dashboard HR berhasil diambil"),
                        new OA\Property(property: "data", type: "object")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated"),
            new OA\Response(response: 403, description: "Forbidden"),
            new OA\Response(response: 500, description: "Server Error"),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $openVacancies = Vacancies::where("status", "open")->count();
        $newApplication = Application::whereDate("created_at", today())->count();
        $needScreening = Application::where("status", "applied")->count();
        $upcomingInterviewsCount = InterviewSession::where("interview_date", ">=", now())->count();

        $pipelineCounts = Application::selectRaw("status, COUNT(*) as total")->groupBy("status")->pluck("total", "status");

        $pipeline = [
            "applied" => (int) ($pipelineCounts["applied"] ?? 0),
            "screening" => (int) ($pipelineCounts["screening"] ?? 0),
            "interview_scheduled" => (int) ($pipelineCounts["interview_scheduled"] ?? 0),
            "interview_done" => (int) ($pipelineCounts["interview_done"] ?? 0),
            "hired" => (int) ($pipelineCounts["hired"] ?? 0),
            "rejected" => (int) ($pipelineCounts["rejected"] ?? 0)
        ];

        $upcomingInterviewsRaw = InterviewSession::with(["application.candidate", "application.vacancy.position", "interviewer"])->where("interview_date", ">=", now())->orderBy("interview_date")->limit(6)->get();

        $upcomingInterviews = $upcomingInterviewsRaw->map(function ($session) {
            return [
                "candidateName" => $session->application->candidate->name ?? "-",
                "positionName" => $session->application->vacancy->position->position_name ?? "-",
                "interviewerName" => $session->interviewer->name ?? "-",
                "notes" => $session->notes ?? "",
                "date" => $session->interview_date,
            ];
        });

        $vacanciesClosingRaw = Vacancies::with(["position.department"])->withCount("applications")->where("status", "open")->whereDate("deadline", ">=", today())->orderBy("deadline")->limit(6)->get();

        $vacanciesClosingSoon = $vacanciesClosingRaw->map(function ($vacancy) {
            return [
                "title" => $vacancy->title,
                "positionName" => $vacancy->position->position_name,
                "departmentName" => $vacancy->position->department->department_name,
                "applicationsCount" => $vacancy->applications_count,
                "deadline" => $vacancy->deadline
            ];
        });

        $latestApplicationsRaw = Application::with(["candidate", "vacancy.position"])->orderByDesc("created_at")->limit(6)->get();

        $latestApplications = $latestApplicationsRaw->map(function ($app) {
            return [
                "candidateName" => $app->candidate->name ?? "-",
                "applicationCode" => $app->application_code,
                "vacancyTitle" => $app->vacancy->title ?? "-",
                "positionName" => $app->vacancy->position->position_name ?? "-",
                "status" => $app->status
            ];
        });

        return response()->json([
            "success" => true,
            "message" => "Data dashboard HR berhasil diambil",
            "data" => [
                "stats" => [
                    "open_vacancies" => $openVacancies,
                    "new_application" => $newApplication,
                    "need_screening" => $needScreening,
                    "upcoming_interviews" => $upcomingInterviewsCount,
                ],
                "pipeline" => $pipeline,
                "upcoming_interviews" => $upcomingInterviews,
                "vacancies_closing_soon" => $vacanciesClosingSoon,
                "latest_applications" => $latestApplications
            ]
        ]);
    }
}
