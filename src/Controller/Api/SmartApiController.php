<?php

namespace App\Controller\Api;

use App\Repository\CourseRepository;
use App\Repository\UserRepository;
use App\Service\AiAssistantService;
use App\Service\CourseRecommendationService;
use App\Service\RiskDetectionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/smart')]
class SmartApiController extends AbstractController
{
    #[Route('/chatbot', name: 'api_chatbot', methods: ['POST'])]
    public function chatbot(Request $request, AiAssistantService $assistant, CourseRepository $courses): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $context = implode("\n", array_map(fn ($course) => $course->getDescription(), $courses->findAll()));
        $conversation = $assistant->answer($this->getUser(), (string) ($payload['question'] ?? ''), $context);

        return $this->json(['answer' => $conversation->getAnswer(), 'provider' => $conversation->getProvider()]);
    }

    #[Route('/recommendations', name: 'api_recommendations', methods: ['GET'])]
    public function recommendations(CourseRecommendationService $service): JsonResponse
    {
        return $this->json(array_map(fn (array $item) => [
            'id' => $item['course']->getId(),
            'title' => $item['course']->getTitle(),
            'level' => $item['course']->getLevel(),
            'category' => $item['course']->getCategory(),
            'score' => $item['score'],
        ], $service->forStudent($this->getUser())));
    }

    #[Route('/risk/{id}', name: 'api_risk', methods: ['GET'])]
    public function risk(UserRepository $users, RiskDetectionService $service, int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $student = $users->find($id);
        if (!$student) {
            return $this->json(['error' => 'Student not found'], 404);
        }
        $risk = $service->analyze($student);
        $risk['student'] = ['id' => $student->getId(), 'name' => $student->getName(), 'email' => $student->getEmail()];

        return $this->json($risk);
    }
}
