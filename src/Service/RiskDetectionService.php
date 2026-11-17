<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\AbsenceRepository;
use App\Repository\ResultRepository;

class RiskDetectionService
{
    public function __construct(
        private readonly AbsenceRepository $absences,
        private readonly ResultRepository $results,
    ) {}

    public function analyze(User $student): array
    {
        $recentAbsences = $this->absences->countRecentForStudent($student);
        $average = $this->results->averageForStudent($student);
        $daysInactive = $student->getLastLogin() ? $student->getLastLogin()->diff(new \DateTimeImmutable())->days : 999;

        $score = 0;
        $reasons = [];
        if ($recentAbsences >= 3) { $score += 40; $reasons[] = 'Absences frequentes'; }
        if ($average > 0 && $average < 10) { $score += 35; $reasons[] = 'Moyenne faible'; }
        if ($daysInactive >= 14) { $score += 25; $reasons[] = 'Inactivite prolongee'; }

        return [
            'student' => $student,
            'score' => min(100, $score),
            'level' => $score >= 70 ? 'high' : ($score >= 35 ? 'medium' : 'low'),
            'reasons' => $reasons,
            'average' => $average,
            'recentAbsences' => $recentAbsences,
            'daysInactive' => $daysInactive,
        ];
    }
}
