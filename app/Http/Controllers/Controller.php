<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Dokumentasi API",
    description: "Lorem Ipsum"
)]
#[OA\Server(
    url: "http://localhost:8000/api/v1",
    description: "Demo API Server"
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'role', type: 'string', enum: ['admin', 'hr', 'supervisor', 'employee', 'candidate'], example: 'hr'),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive'], example: 'active'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Candidate',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Budi Santoso'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
        new OA\Property(property: 'phone', type: 'string', example: '081234567890'),
        new OA\Property(property: 'gender', type: 'string', enum: ['L', 'P'], example: 'L'),
        new OA\Property(property: 'city', type: 'string', example: 'Jakarta'),
        new OA\Property(property: 'zip_code', type: 'string', example: '12345'),
        new OA\Property(property: 'complete_address', type: 'string', example: 'Jl. Contoh No. 1'),
        new OA\Property(property: 'experience', type: 'string', example: '3 tahun di bidang software development'),
        new OA\Property(property: 'web_portofolio_url', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'Employee',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 3),
        new OA\Property(property: 'department_id', type: 'integer', example: 1),
        new OA\Property(property: 'position_id', type: 'integer', example: 1),
        new OA\Property(property: 'supervisor_id', type: 'integer', nullable: true, example: null),
        new OA\Property(property: 'join_date', type: 'string', format: 'date', example: '2023-01-01'),
        new OA\Property(property: 'contract_status', type: 'string', enum: ['permanent', 'contract', 'probation'], example: 'permanent'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Department',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'department_name', type: 'string', example: 'Teknologi Informasi'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'positions_count', type: 'integer', example: 5),
    ]
)]
#[OA\Schema(
    schema: 'Position',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'department_id', type: 'integer', example: 1),
        new OA\Property(property: 'position_name', type: 'string', example: 'Software Engineer'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
    ]
)]
#[OA\Schema(
    schema: 'RecruitmentCriteria',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'position_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Kemampuan Komunikasi'),
        new OA\Property(property: 'weight', type: 'number', format: 'float', example: 25.0),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', enum: ['benefit', 'cost'], example: 'benefit'),
        new OA\Property(property: 'data_type', type: 'string', enum: ['kualitatif', 'kuantitatif'], example: 'kualitatif'),
    ]
)]
#[OA\Schema(
    schema: 'LikertScale',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'recruitment_criterias_id', type: 'integer', example: 1),
        new OA\Property(property: 'label', type: 'string', example: 'Sangat Baik'),
        new OA\Property(property: 'value', type: 'number', format: 'float', example: 5.0),
    ]
)]
#[OA\Schema(
    schema: 'Vacancy',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'hr_id', type: 'integer', example: 2),
        new OA\Property(property: 'position_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Lowongan Software Engineer 2024'),
        new OA\Property(property: 'description', type: 'string', example: 'Deskripsi posisi...'),
        new OA\Property(property: 'requirements', type: 'string', example: 'Min. S1 Teknik Informatika...'),
        new OA\Property(property: 'deadline', type: 'string', format: 'date', example: '2024-12-31'),
        new OA\Property(property: 'status', type: 'string', enum: ['open', 'closed'], example: 'open'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Application',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'candidate_id', type: 'integer', example: 1),
        new OA\Property(property: 'vacancy_id', type: 'integer', example: 1),
        new OA\Property(property: 'application_code', type: 'string', example: 'APP-2024-ABCDEF'),
        new OA\Property(property: 'status', type: 'string', enum: ['applied', 'screening', 'interview_scheduled', 'interview_done', 'hired', 'rejected'], example: 'applied'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'InterviewSession',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'application_id', type: 'integer', example: 1),
        new OA\Property(property: 'interviewer_id', type: 'integer', example: 2),
        new OA\Property(property: 'interview_date', type: 'string', format: 'date-time', example: '2024-12-15 10:00:00'),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'total', type: 'integer', example: 100),
        new OA\Property(property: 'last_page', type: 'integer', example: 7),
    ]
)]
#[OA\Schema(
    schema: 'SuccessResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: true),
        new OA\Property(property: 'message', type: 'string', example: 'Operasi berhasil.'),
    ]
)]
#[OA\Schema(
    schema: 'ErrorResponse',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Terjadi kesalahan.'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(property: 'errors', type: 'object', additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'string'))),
    ]
)]
abstract class Controller
{
    /**
     * Standarisasi API response.
     *
     * @param  bool   $success
     * @param  string $message
     * @param  mixed  $data     null = not found / no payload, array/object = payload
     * @param  int    $status   HTTP status code
     */
    protected function apiResponse(
        bool $success,
        string $message,
        mixed $data = null,
        int $status = 200
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
}
