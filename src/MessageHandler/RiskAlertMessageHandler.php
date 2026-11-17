<?php

namespace App\MessageHandler;

use App\Message\RiskAlertMessage;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Service\RiskDetectionService;
use App\Service\SmartMailerService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RiskAlertMessageHandler
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RiskDetectionService $riskDetection,
        private readonly NotificationService $notifications,
        private readonly SmartMailerService $mailer,
    ) {}

    public function __invoke(RiskAlertMessage $message): void
    {
        $student = $this->users->find($message->studentId);
        if (!$student) {
            return;
        }
        $risk = $this->riskDetection->analyze($student);
        if ($risk['score'] < 70) {
            return;
        }
        $this->notifications->notify($student, 'Alerte accompagnement', 'Votre activite montre un risque eleve. Un enseignant va vous accompagner.');
        $this->mailer->send($student, 'Alerte SmartLearn Academy', 'emails/risk_alert.html.twig', ['risk' => $risk]);
    }
}
