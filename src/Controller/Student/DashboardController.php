<?php

namespace App\Controller\Student;

use App\Entity\Message;
use App\Repository\AbsenceRepository;
use App\Repository\ConversationRepository;
use App\Repository\CourseRepository;
use App\Repository\ExamRepository;
use App\Repository\MessageRepository;
use App\Repository\ResultRepository;
use App\Service\AiAssistantService;
use App\Service\CourseRecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/student')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'student_dashboard')]
    public function index(CourseRepository $courses, CourseRecommendationService $recommendations): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        return $this->render('student/dashboard.html.twig', [
            'courses' => $courses->findAll(),
            'recommendations' => $recommendations->forStudent($user),
        ]);
    }

    #[Route('/courses', name: 'student_courses')]
    public function courses(CourseRepository $courses): Response
    {
        return $this->render('student/courses.html.twig', ['courses' => $courses->findAll()]);
    }

    #[Route('/absences', name: 'student_absences')]
    public function absences(AbsenceRepository $absences): Response
    {
        return $this->render('student/table.html.twig', ['title' => 'Mes absences', 'items' => $absences->findBy(['student' => $this->getUser()], ['dateAbsence' => 'DESC'])]);
    }

    #[Route('/exams', name: 'student_exams')]
    public function exams(ExamRepository $exams, ResultRepository $results): Response
    {
        return $this->render('student/exams.html.twig', ['exams' => $exams->findAll(), 'results' => $results->findBy(['student' => $this->getUser()])]);
    }

    #[Route('/chatbot', name: 'student_chatbot')]
    public function chatbot(ConversationRepository $history): Response
    {
        return $this->render('student/chatbot.html.twig', ['history' => $history->findBy(['student' => $this->getUser()], ['createdAt' => 'DESC'], 20)]);
    }

    #[Route('/chatbot/ask', name: 'student_chatbot_ask', methods: ['POST'])]
    public function ask(Request $request, AiAssistantService $assistant, CourseRepository $courses): JsonResponse
    {
        $question = trim((string) $request->request->get('question', ''));
        if ($question === '') {
            return $this->json(['error' => 'Question vide'], 422);
        }
        $context = implode("\n", array_map(fn ($course) => $course->getTitle().': '.$course->getDescription(), $courses->findAll()));
        $conversation = $assistant->answer($this->getUser(), $question, $context);

        return $this->json(['answer' => $conversation->getAnswer(), 'provider' => $conversation->getProvider()]);
    }

    #[Route('/chat', name: 'student_chat')]
    public function chat(MessageRepository $messages, Request $request, EntityManagerInterface $em, ?HubInterface $hub = null): Response
    {
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('student_chat', (string) $request->request->get('_csrf_token'))) {
            $message = (new Message())->setSender($this->getUser())->setRoom('teacher')->setContent((string) $request->request->get('content', ''));
            $em->persist($message);
            $em->flush();
            if ($hub) {
                try {
                    $hub->publish(new Update('/chat/teacher', json_encode(['from' => $this->getUser()?->getUserIdentifier(), 'content' => $message->getContent()], JSON_THROW_ON_ERROR)));
                } catch (\Throwable) {}
            }
            return $this->redirectToRoute('student_chat');
        }

        return $this->render('student/chat.html.twig', ['messages' => $messages->findBy(['room' => 'teacher'], ['createdAt' => 'ASC'], 50)]);
    }
}
