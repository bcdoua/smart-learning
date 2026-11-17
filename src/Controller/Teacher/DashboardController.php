<?php

namespace App\Controller\Teacher;

use App\Entity\Absence;
use App\Entity\Course;
use App\Entity\Exam;
use App\Entity\Message;
use App\Entity\Result;
use App\Form\AbsenceType;
use App\Form\CourseType;
use App\Form\ExamType;
use App\Form\MessageType;
use App\Form\ResultType;
use App\Repository\AbsenceRepository;
use App\Repository\CourseRepository;
use App\Repository\ExamRepository;
use App\Repository\MessageRepository;
use App\Repository\ResultRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/teacher')]
class DashboardController extends AbstractController
{
    // =========================================================
    // DASHBOARD
    // =========================================================

    #[Route('', name: 'teacher_dashboard')]
    public function index(CourseRepository $courses, AbsenceRepository $absences, ExamRepository $exams): Response
    {
        $teacher = $this->getUser();
        return $this->render('teacher/dashboard.html.twig', [
            'courses' => $courses->findBy(['teacher' => $teacher]),
            'stats'   => [
                'courses'  => $courses->count(['teacher' => $teacher]),
                'absences' => $absences->count([]),
                'exams'    => $exams->count([]),
            ],
        ]);
    }

    // =========================================================
    // COURSES
    // =========================================================

    #[Route('/courses', name: 'teacher_courses')]
    public function courses(CourseRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $teacher = $this->getUser();
        $qb = $repo->createQueryBuilder('c')
            ->where('c.teacher = :teacher')
            ->setParameter('teacher', $teacher)
            ->orderBy('c.createdAt', 'DESC');

        return $this->render('teacher/list.html.twig', [
            'title'        => 'Mes cours',
            'icon'         => 'fa-book-open',
            'route_new'    => 'teacher_course_new',
            'items'        => $paginator->paginate($qb, $request->query->getInt('page', 1), 10),
            'columns'      => ['title' => 'Titre', 'level' => 'Niveau', 'category' => 'Catégorie'],
            'edit_route'   => 'teacher_course_edit',
            'delete_route' => 'teacher_course_delete',
            'entity_name'  => 'course',
        ]);
    }

    #[Route('/courses/new', name: 'teacher_course_new')]
    #[Route('/courses/{id}/edit', name: 'teacher_course_edit')]
    public function courseForm(?Course $course, Request $request, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        $course ??= (new Course())->setTeacher($this->getUser());
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($course);
            $em->flush();
            foreach ($course->getEnrollments() as $enrollment) {
                $notifications->notify($enrollment->getStudent(), 'Cours mis à jour', $course->getTitle());
            }
            $this->addFlash('success', 'Cours sauvegardé.');
            return $this->redirectToRoute('teacher_courses');
        }
        return $this->render('teacher/form.html.twig', ['title' => 'Cours', 'form' => $form]);
    }

    #[Route('/courses/{id}/delete', name: 'teacher_course_delete', methods: ['POST'])]
    public function courseDelete(Course $course, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_course_'.$course->getId(), (string) $request->request->get('_token'))) {
            $em->remove($course);
            $em->flush();
            $this->addFlash('success', 'Cours supprimé.');
        }
        return $this->redirectToRoute('teacher_courses');
    }

    // =========================================================
    // ABSENCES
    // =========================================================

    #[Route('/absences', name: 'teacher_absences')]
    public function absences(AbsenceRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $qb = $repo->createQueryBuilder('a')->orderBy('a.dateAbsence', 'DESC');

        return $this->render('teacher/list.html.twig', [
            'title'        => 'Absences',
            'icon'         => 'fa-calendar-times',
            'route_new'    => 'teacher_absence_new',
            'items'        => $paginator->paginate($qb, $request->query->getInt('page', 1), 10),
            'columns'      => ['student.name' => 'Étudiant', 'course.title' => 'Cours', 'dateAbsence' => 'Date', 'status' => 'Statut'],
            'edit_route'   => 'teacher_absence_edit',
            'delete_route' => 'teacher_absence_delete',
            'entity_name'  => 'absence',
        ]);
    }

    #[Route('/absences/new', name: 'teacher_absence_new')]
    #[Route('/absences/{id}/edit', name: 'teacher_absence_edit')]
    public function absenceForm(?Absence $absence, Request $request, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        $absence ??= new Absence();
        $form = $this->createForm(AbsenceType::class, $absence);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($absence);
            $em->flush();
            if ($absence->getStudent()) {
                $notifications->notify($absence->getStudent(), 'Absence marquée', 'Votre enseignant a signalé une absence.');
            }
            $this->addFlash('success', 'Absence sauvegardée.');
            return $this->redirectToRoute('teacher_absences');
        }
        return $this->render('teacher/form.html.twig', ['title' => 'Absence', 'form' => $form]);
    }

    #[Route('/absences/{id}/delete', name: 'teacher_absence_delete', methods: ['POST'])]
    public function absenceDelete(Absence $absence, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_absence_'.$absence->getId(), (string) $request->request->get('_token'))) {
            $em->remove($absence);
            $em->flush();
            $this->addFlash('success', 'Absence supprimée.');
        }
        return $this->redirectToRoute('teacher_absences');
    }

    // =========================================================
    // EXAMS
    // =========================================================

    #[Route('/exams', name: 'teacher_exams')]
    public function exams(ExamRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $qb = $repo->createQueryBuilder('e')->orderBy('e.id', 'DESC');

        return $this->render('teacher/list.html.twig', [
            'title'        => 'Examens',
            'icon'         => 'fa-clipboard-check',
            'route_new'    => 'teacher_exam_new',
            'items'        => $paginator->paginate($qb, $request->query->getInt('page', 1), 10),
            'columns'      => ['title' => 'Titre', 'duration' => 'Durée (min)', 'passingScore' => 'Score min'],
            'edit_route'   => 'teacher_exam_edit',
            'delete_route' => 'teacher_exam_delete',
            'entity_name'  => 'exam',
        ]);
    }

    #[Route('/exams/new', name: 'teacher_exam_new')]
    #[Route('/exams/{id}/edit', name: 'teacher_exam_edit')]
    public function examForm(?Exam $exam, Request $request, EntityManagerInterface $em): Response
    {
        $exam ??= new Exam();
        $form = $this->createForm(ExamType::class, $exam);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($exam);
            $em->flush();
            $this->addFlash('success', 'Examen sauvegardé.');
            return $this->redirectToRoute('teacher_exams');
        }
        return $this->render('teacher/form.html.twig', ['title' => 'Examen', 'form' => $form]);
    }

    #[Route('/exams/{id}/delete', name: 'teacher_exam_delete', methods: ['POST'])]
    public function examDelete(Exam $exam, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_exam_'.$exam->getId(), (string) $request->request->get('_token'))) {
            $em->remove($exam);
            $em->flush();
            $this->addFlash('success', 'Examen supprimé.');
        }
        return $this->redirectToRoute('teacher_exams');
    }

    // =========================================================
    // RESULTS / NOTES
    // =========================================================

    #[Route('/results', name: 'teacher_results')]
    public function results(ResultRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $qb = $repo->createQueryBuilder('r')->orderBy('r.id', 'DESC');

        return $this->render('teacher/list.html.twig', [
            'title'        => 'Notes publiées',
            'icon'         => 'fa-poll',
            'route_new'    => 'teacher_result_new',
            'items'        => $paginator->paginate($qb, $request->query->getInt('page', 1), 10),
            'columns'      => ['student.name' => 'Étudiant', 'exam.title' => 'Examen', 'score' => 'Note', 'status' => 'Statut'],
            'edit_route'   => 'teacher_result_edit',
            'delete_route' => 'teacher_result_delete',
            'entity_name'  => 'result',
        ]);
    }

    #[Route('/results/new', name: 'teacher_result_new')]
    #[Route('/results/{id}/edit', name: 'teacher_result_edit')]
    public function resultForm(?Result $result, Request $request, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        $result ??= new Result();
        $form = $this->createForm(ResultType::class, $result);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($result);
            $em->flush();
            if ($result->getStudent()) {
                $notifications->notify($result->getStudent(), 'Note publiée', 'Un résultat vient d\'être publié.');
            }
            $this->addFlash('success', 'Note publiée.');
            return $this->redirectToRoute('teacher_results');
        }
        return $this->render('teacher/form.html.twig', ['title' => 'Note', 'form' => $form]);
    }

    #[Route('/results/{id}/delete', name: 'teacher_result_delete', methods: ['POST'])]
    public function resultDelete(Result $result, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_result_'.$result->getId(), (string) $request->request->get('_token'))) {
            $em->remove($result);
            $em->flush();
            $this->addFlash('success', 'Note supprimée.');
        }
        return $this->redirectToRoute('teacher_results');
    }

    // =========================================================
    // CHAT
    // =========================================================

    #[Route('/chat', name: 'teacher_chat')]
    public function chat(MessageRepository $messages, Request $request, EntityManagerInterface $em, ?HubInterface $hub = null): Response
    {
        $message = (new Message())->setSender($this->getUser())->setRoom('teacher');
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $em->flush();
            if ($hub) {
                try {
                    $hub->publish(new Update('/chat/teacher', json_encode([
                        'from'    => $this->getUser()?->getUserIdentifier(),
                        'content' => $message->getContent(),
                    ], JSON_THROW_ON_ERROR)));
                } catch (\Throwable) {}
            }
            return $this->redirectToRoute('teacher_chat');
        }
        return $this->render('teacher/chat.html.twig', [
            'form'     => $form,
            'messages' => $messages->findBy(['room' => 'teacher'], ['createdAt' => 'ASC'], 50),
        ]);
    }
}
