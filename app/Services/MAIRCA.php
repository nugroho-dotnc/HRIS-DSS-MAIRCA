<?php

namespace App\Services;

use App\Models\Application;
use App\Models\RecruitmentCriteria;
use App\Models\Vacancies;

class MAIRCA
{
    private function normalizeMatrix(array $decisionMatrix, array $criteriaTypes): array
    {
        $alternativeCount = count($decisionMatrix);
        $criteriaCount = count($decisionMatrix[0]);
        $normalizedMatrix = [];
        for ($j = 0; $j < $criteriaCount; $j++) {
            $column = array_column($decisionMatrix, $j);
            $min = min($column);
            $max = max($column);
            for ($i = 0; $i < $alternativeCount; $i++) {
                $currentValue = $decisionMatrix[$i][$j];
                if ($max - $min == 0) {
                    $normalizedMatrix[$i][$j] = 0;
                } elseif ($criteriaTypes[$j] == 'benefit') {
                    $normalizedMatrix[$i][$j] = ($currentValue - $min) / ($max - $min);
                } else {
                    $normalizedMatrix[$i][$j] = ($max - $currentValue) / ($max - $min);
                }
            }
        }
        return $normalizedMatrix;
    }

    private function calculateTheoreticalMatrix(array $decisionMatrix, float $equalPreference, array $criteriaWeights): array
    {
        $alternativeCount = count($decisionMatrix);
        $criteriaCount = count($decisionMatrix[0]);
        $theoreticalMatrix = [];
        for ($i = 0; $i < $alternativeCount; $i++) {
            for ($j = 0; $j < $criteriaCount; $j++) {
                $theoreticalMatrix[$i][$j] = $equalPreference * $criteriaWeights[$j];
            }
        }
        return $theoreticalMatrix;
    }

    private function calculateActualMatrix(array $theoreticalMatrix, array $normalizedMatrix): array
    {
        $alternativeCount = count($normalizedMatrix);
        $criteriaCount = count($normalizedMatrix[0]);
        $actualMatrix = [];
        for ($i = 0; $i < $alternativeCount; $i++) {
            for ($j = 0; $j < $criteriaCount; $j++) {
                $actualMatrix[$i][$j] = $theoreticalMatrix[$i][$j] * $normalizedMatrix[$i][$j];
            }
        }
        return $actualMatrix;
    }

    private function calculateQiScores(array $gapMatrix): array
    {
        $alternativeCount = count($gapMatrix);
        $qiScores = [];
        for ($i = 0; $i < $alternativeCount; $i++) {
            $qiScores[$i] = array_sum($gapMatrix[$i]);
        }
        return $qiScores;
    }

    private function calculateGapMatrix(array $theoreticalMatrix, array $actualMatrix): array
    {
        $alternativeCount = count($theoreticalMatrix);
        $criteriaCount = count($theoreticalMatrix[0]);
        $gapMatrix = [];
        for ($i = 0; $i < $alternativeCount; $i++) {
            for ($j = 0; $j < $criteriaCount; $j++) {
                $gapMatrix[$i][$j] = abs($theoreticalMatrix[$i][$j] - $actualMatrix[$i][$j]);
            }
        }
        return $gapMatrix;
    }

    public function calculate(int $vacancyId): array
    {
        // ambil vacancy beserta posisinya dan departemennya
        $vacancy = Vacancies::with("position.department")->findOrFail($vacancyId);
        $positionId = $vacancy->position->id;
        $department = $vacancy->position->department->department_name;

        // ambil kriterianya
        $criteriaList = RecruitmentCriteria::where("position_id", $positionId)->orderBy("id")->get();

        // validasi kriteria
        if ($criteriaList->isEmpty()) {
            throw new \RuntimeException("Kriteria penilaian belum dikonfigurasi untuk posisi jabatan ini.");
        }

        // ambil semua application yang sudah selesai interview
        $applications = Application::where("vacancy_id", $vacancyId)->whereIn("status", ["interview_done", "hired", "rejected"])->with(["candidate", "interviewSessions.scores"])->get();

        // validasi
        if ($applications->isEmpty()) {
            throw new \RuntimeException("Belum ada kandidat dengan status interview_done, hired, atau rejected.");
        }

        // perhitungan MAIRCA
        $alternatives = [];
        $criteriaNames = [];
        $criteriaTypes = [];
        $criteriaWeights = [];

        // isi $criteriaList
        foreach ($criteriaList as $c) {
            $criteriaNames[] = $c->name;
            $criteriaTypes[] = $c->type;
            $criteriaWeights[] = $c->weight;
        }

        // isi $alternatives
        foreach ($applications as $app) {
            $alternatives[] = $app->candidate->name;
        }

        $equalPreference = 1 / count($alternatives);

        // buat decision matrix
        $decisionMatrix = [];
        foreach ($applications as $i => $app) {
            $lastSession = $app->interviewSessions->sortBy("id")->last();

            foreach ($criteriaList as $j => $c) {
                if ($lastSession) {
                    $scoreRecord = $lastSession->scores->firstWhere("criteria_id", $c->id);
                    $decisionMatrix[$i][$j] = $scoreRecord ? (float) $scoreRecord->score : 0;
                } else {
                    $decisionMatrix[$i][$j] = 0;
                }
            }
        }

        $normalizedMatrix = $this->normalizeMatrix($decisionMatrix, $criteriaTypes);
        $theoreticalMatrix = $this->calculateTheoreticalMatrix($decisionMatrix, $equalPreference, $criteriaWeights);
        $actualMatrix = $this->calculateActualMatrix($theoreticalMatrix, $normalizedMatrix);
        $gapMatrix = $this->calculateGapMatrix($theoreticalMatrix, $actualMatrix);
        $qiScores = $this->calculateQiScores($gapMatrix);

        $alternativesIds = [];
        foreach ($applications as $app) {
            $alternativesIds[] = $app->id;
        }

        $indexedQi = array_combine(array_keys($alternatives), $qiScores);
        asort($indexedQi);

        $ranking = [];
        $rank = 1;
        foreach ($indexedQi as $i => $qi) {
            $ranking[] = [
                "rank" => $rank,
                "candidate_name" => $alternatives[$i],
                "application_id" => $alternativesIds[$i],
                "status" => $applications[$i]->status,
                "qi_score" => round($qi, 6),
                "gap_details" => $gapMatrix[$i]
            ];
            $rank++;
        }

        return [
            "vacancy" => $vacancy->title,
            "position" => $vacancy->position->position_name,
            "department" => $department,
            "deadline" => $vacancy->deadline,
            "alternatives" => $alternatives,
            "criteria" => $criteriaNames,
            "weights" => $criteriaWeights,
            "types" => $criteriaTypes,
            "Pi" => $equalPreference,
            "decision_matrix" => $decisionMatrix,
            "normalized_matrix" => $normalizedMatrix,
            "theoretical_matrix" => $theoreticalMatrix,
            "actual_matrix" => $actualMatrix,
            "gap_matrix" => $gapMatrix,
            "qi_scores" => $qiScores,
            "ranking" => $ranking,
        ];
    }
}
