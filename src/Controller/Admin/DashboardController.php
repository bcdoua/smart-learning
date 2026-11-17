<?php

namespace App\Controller\Admin;

use App\Entity\Absence;
use App\Entity\Course;
use App\Entity\Exam;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Result;
use App\Entity\User;
use App\Form\AbsenceType;
use App\Form\CourseType;
use App\Form\ExamType;
use App\Form\MessageType;
use App\Form\NotificationType;
use App\Form\ResultType;
use App\Form\UserType;
use App\Repository\AbsenceRepository;
use App\Repository\ActivityLogRepository;
use App\Repository\CourseRepository;
use App\Repository\ExamRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\ResultRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use App\Service\RiskDetectionService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    
    #[Route('', name: 'admin_dashboard')]
    public function index(
        UserRepository $users,
        CourseRepository $courses,
        AbsenceRepository $absences,
        ExamRepository $exams,
        ActivityLogRepository $logs
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'students' => $users->countByRole('ROLE_STUDENT'),
                'teachers' => $users->countByRole('ROLE_TEACHER'),
                'courses'  => $courses->count([]),
                'absences' => $absences->count([]),
                'exams'    => $exams->count([]),
            ],
            'courses' => $courses->popular(5),
            'logs'    => $logs->findBy([], ['createdAt' => 'DESC'], 8),
        ]);
    }

    // =========================================================
    // USERS
    // =========================================================

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $repo->createQueryBuilder('u')->orderBy('u.createdAt', 'DESC');
        if ($search = $request->query->get('q')) {
            $query->andWhere('u.name LIKE :q OR u.email LIKE :q')->setParameter('q', '%'.$search.'%');
        }

        return $this->render('admin/list.html.twig', [
            'title'        => 'Utilisateurs',
            'route_new'    => 'admin_user_new',
            'items'        => $paginator->paginate($query, $request->query->getInt('page', 1), 10),
            'columns'      => ['name' => 'Nom', 'email' => 'Email', 'rolesLabel' => 'Rôles'],
            'edit_route'   => 'admin_user_edit',
            'delete_route' => 'admin_user_delete',
            'entity_name'  => 'user',
        ]);
    }

    #[Route('/users/new', name: 'admin_user_new')]
    #[Route('/users/{id}/edit', name: 'admin_user_edit')]
    public function userForm(?User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user ??= new User();
        $form = $this->createForm(UserType::class, $user, ['edit' => null !== $user->getId()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $this->upload($form, 'imageFile', fn (string $name) => $user->setImage($name));
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur sauvegardé.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/form.html.twig', ['title' => 'Utilisateur', 'form' => $form]);
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function userDelete(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_user_'.$user->getId(), (string) $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }
        return $this->redirectToRoute('admin_users');
    }

    // =========================================================
    // COURSES
    // =========================================================

    #[Route('/courses', name: 'admin_courses')]
    public function courses(CourseRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/list.html.twig', [
            'title'        => 'Cours',
            'route_new'    => 'admin_course_new',
            'items'        => $paginator->paginate($repo->createQueryBuilder('c')->orderBy('c.createdAt', 'DESC'), $request->query->getInt('page', 1), 10),
            'columns'      => ['title' => 'Titre', 'level' => 'Niveau', 'category' => 'Catégorie'],
            'edit_route'   => 'admin_course_edit',
            'delete_route' => 'admin_course_delete',
            'entity_name'  => 'course',
        ]);
    }

    #[Route('/courses/new', name: 'admin_course_new')]
    #[Route('/courses/{id}/edit', name: 'admin_course_edit')]
    public function courseForm(?Course $course, Request $request, EntityManagerInterface $em): Response
    {
        $course ??= new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->upload($form, 'imageFile', fn (string $name) => $course->setImage($name));
            $this->upload($form, 'pdfFile',   fn (string $name) => $course->setPdf($name));
            $this->upload($form, 'videoFile', fn (string $name) => $course->setVideo($name));
            $em->persist($course);
            $em->flush();
            $this->addFlash('success', 'Cours sauvegardé.');
            return $this->redirectToRoute('admin_courses');
        }
        return $this->render('admin/form.html.twig', ['title' => 'Cours', 'form' => $form]);
    }

    #[Route('/courses/{id}/delete', name: 'admin_course_delete', methods: ['POST'])]
    public function courseDelete(Course $course, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_course_'.$course->getId(), (string) $request->request->get('_token'))) {
            $em->remove($course);
            $em->flush();
            $this->addFlash('success', 'Cours supprimé.');
        }
        return $this->redirectToRoute('admin_courses');
    }

    // =========================================================
    // ABSENCES
    // =========================================================

    #[Route('/absences', name: 'admin_absences')]
    public function absences(AbsenceRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/list.html.twig', [
            'title'        => 'Absences',
            'route_new'    => 'admin_absence_new',
            'items'        => $paginator->paginate($repo->createQueryBuilder('a')->orderBy('a.dateAbsence', 'DESC'), $request->query->getInt('page', 1), 10),
            'columns'      => ['student.name' => 'Étudiant', 'course.title' => 'Cours', 'status' => 'Statut'],
            'edit_route'   => 'admin_absence_edit',
            'delete_route' => 'admin_absence_delete',
            'entity_name'  => 'absence',
        ]);
    }

    #[Route('/absences/new', name: 'admin_absence_new')]
    #[Route('/absences/{id}/edit', name: 'admin_absence_edit')]
    public function absenceForm(?Absence $absence, Request $request, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        $absence ??= new Absence();
        $form = $this->createForm(AbsenceType::class, $absence);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($absence);
            $em->flush();
            if ($absence->getStudent()) {
                $notifications->notify($absence->getStudent(), 'Absence marquée', 'Une absence a été ajoutée à votre dossier.');
            }
            $this->addFlash('success', 'Absence sauvegardée.');
            return $this->redirectToRoute('admin_absences');
        }
        return $this->render('admin/form.html.twig', ['title' => 'Absence', 'form' => $form]);
    }

    #[Route('/absences/{id}/delete', name: 'admin_absence_delete', methods: ['POST'])]
    public function absenceDelete(Absence $absence, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_absence_'.$absence->getId(), (string) $request->request->get('_token'))) {
            $em->remove($absence);
            $em->flush();
            $this->addFlash('success', 'Absence supprimée.');
        }
        return $this->redirectToRoute('admin_absences');
    }

    // =========================================================
    // EXAMS
    // =========================================================

    #[Route('/exams', name: 'admin_exams')]
    public function exams(ExamRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/list.html.twig', [
            'title'        => 'Examens',
            'route_new'    => 'admin_exam_new',
            'items'        => $paginator->paginate($repo->createQueryBuilder('e')->orderBy('e.id', 'DESC'), $request->query->getInt('page', 1), 10),
            'columns'      => ['title' => 'Titre', 'duration' => 'Durée', 'passingScore' => 'Score min'],
            'edit_route'   => 'admin_exam_edit',
            'delete_route' => 'admin_exam_delete',
            'entity_name'  => 'exam',
        ]);
    }

    #[Route('/exams/new', name: 'admin_exam_new')]
    #[Route('/exams/{id}/edit', name: 'admin_exam_edit')]
    public function examForm(?Exam $exam, Request $request, EntityManagerInterface $em): Response
    {
        $exam ??= new Exam();
        return $this->handleEntityForm($exam, ExamType::class, $request, $em, 'admin_exams', 'Examen');
    }

    #[Route('/exams/{id}/delete', name: 'admin_exam_delete', methods: ['POST'])]
    public function examDelete(Exam $exam, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_exam_'.$exam->getId(), (string) $request->request->get('_token'))) {
            $em->remove($exam);
            $em->flush();
            $this->addFlash('success', 'Examen supprimé.');
        }
        return $this->redirectToRoute('admin_exams');
    }

    // =========================================================
    // RESULTS
    // =========================================================

    #[Route('/results', name: 'admin_results')]
    public function results(ResultRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/list.html.twig', [
            'title'        => 'Résultats',
            'route_new'    => 'admin_result_new',
            'items'        => $paginator->paginate($repo->createQueryBuilder('r')->orderBy('r.id', 'DESC'), $request->query->getInt('page', 1), 10),
            'columns'      => ['student.name' => 'Étudiant', 'exam.title' => 'Examen', 'score' => 'Score', 'status' => 'Statut'],
            'edit_route'   => 'admin_result_edit',
            'delete_route' => 'admin_result_delete',
            'entity_name'  => 'result',
        ]);
    }

    #[Route('/results/new', name: 'admin_result_new')]
    #[Route('/results/{id}/edit', name: 'admin_result_edit')]
    public function resultForm(?Result $result, Request $request, EntityManagerInterface $em, NotificationService $notifications): Response
    {
        $result ??= new Result();
        $form = $this->createForm(ResultType::class, $result);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($result);
            $em->flush();
            if ($result->getStudent()) {
                $notifications->notify($result->getStudent(), 'Résultat publié', 'Votre note est disponible.');
            }
            $this->addFlash('success', 'Résultat sauvegardé.');
            return $this->redirectToRoute('admin_results');
        }
        return $this->render('admin/form.html.twig', ['title' => 'Résultat', 'form' => $form]);
    }

    #[Route('/results/{id}/delete', name: 'admin_result_delete', methods: ['POST'])]
    public function resultDelete(Result $result, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_result_'.$result->getId(), (string) $request->request->get('_token'))) {
            $em->remove($result);
            $em->flush();
            $this->addFlash('success', 'Résultat supprimé.');
        }
        return $this->redirectToRoute('admin_results');
    }

    // =========================================================
    // NOTIFICATIONS
    // =========================================================

    #[Route('/notifications', name: 'admin_notifications')]
    public function notifications(NotificationRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/list.html.twig', [
            'title'        => 'Notifications',
            'route_new'    => 'admin_notification_new',
            'items'        => $paginator->paginate($repo->createQueryBuilder('n')->orderBy('n.createdAt', 'DESC'), $request->query->getInt('page', 1), 10),
            'columns'      => ['user.name' => 'Utilisateur', 'title' => 'Titre', 'message' => 'Message'],
            'edit_route'   => 'admin_notification_edit',
            'delete_route' => 'admin_notification_delete',
            'entity_name'  => 'notification',
        ]);
    }

    #[Route('/notifications/new', name: 'admin_notification_new')]
    #[Route('/notifications/{id}/edit', name: 'admin_notification_edit')]
    public function notificationForm(?Notification $notification, Request $request, EntityManagerInterface $em): Response
    {
        $notification ??= new Notification();
        return $this->handleEntityForm($notification, NotificationType::class, $request, $em, 'admin_notifications', 'Notification');
    }

    #[Route('/notifications/{id}/delete', name: 'admin_notification_delete', methods: ['POST'])]
    public function notificationDelete(Notification $notification, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_notification_'.$notification->getId(), (string) $request->request->get('_token'))) {
            $em->remove($notification);
            $em->flush();
            $this->addFlash('success', 'Notification supprimée.');
        }
        return $this->redirectToRoute('admin_notifications');
    }

    // =========================================================
    // MESSAGES
    // =========================================================

    #[Route('/messages', name: 'admin_messages')]
    public function messages(MessageRepository $repo, PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/list.html.twig', [
            'title'        => 'Messages',
            'route_new'    => 'admin_message_new',
            'items'        => $paginator->paginate($repo->createQueryBuilder('m')->orderBy('m.createdAt', 'DESC'), $request->query->getInt('page', 1), 10),
            'columns'      => ['sender.name' => 'Expéditeur', 'receiver.name' => 'Récepteur', 'content' => 'Message'],
            'edit_route'   => 'admin_message_edit',
            'delete_route' => 'admin_message_delete',
            'entity_name'  => 'message',
        ]);
    }

    #[Route('/messages/new', name: 'admin_message_new')]
    #[Route('/messages/{id}/edit', name: 'admin_message_edit')]
    public function messageForm(?Message $message, Request $request, EntityManagerInterface $em): Response
    {
        $message ??= new Message();
        return $this->handleEntityForm($message, MessageType::class, $request, $em, 'admin_messages', 'Message');
    }

    #[Route('/messages/{id}/delete', name: 'admin_message_delete', methods: ['POST'])]
    public function messageDelete(Message $message, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_message_'.$message->getId(), (string) $request->request->get('_token'))) {
            $em->remove($message);
            $em->flush();
            $this->addFlash('success', 'Message supprimé.');
        }
        return $this->redirectToRoute('admin_messages');
    }

    // =========================================================
    // RISK / REPORTS
    // =========================================================

    #[Route('/risk', name: 'admin_risk')]
    public function risk(UserRepository $users, RiskDetectionService $risk): Response
    {
        $students = $users->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_STUDENT%')
            ->getQuery()->getResult();

        return $this->render('admin/risk.html.twig', [
            'items' => array_map(fn (User $u) => $risk->analyze($u), $students),
        ]);
    }

    #[Route('/reports/pdf', name: 'admin_reports_pdf')]
    public function pdf(UserRepository $users, CourseRepository $courses): Response
    {
        $dompdf = new Dompdf();
        $dompdf->loadHtml($this->renderView('admin/report_pdf.html.twig', [
            'users'   => $users->count([]),
            'courses' => $courses->count([]),
        ]));
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="smartlearn-report.pdf"',
        ]);
    }

    #[Route('/export/users.csv', name: 'admin_export_users')]
    public function exportUsers(UserRepository $users): Response
    {
        $rows = ['id,name,email,roles'];
        foreach ($users->findAll() as $user) {
            $rows[] = sprintf('%d,"%s","%s","%s"',
                $user->getId(), $user->getName(),
                $user->getEmail(), implode('|', $user->getRoles())
            );
        }

        return new Response(implode("\n", $rows), 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function handleEntityForm(object $entity, string $formType, Request $request, EntityManagerInterface $em, string $redirectRoute, string $title): Response
    {
        $form = $this->createForm($formType, $entity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->addFlash('success', $title.' sauvegardé.');
            return $this->redirectToRoute($redirectRoute);
        }
        return $this->render('admin/form.html.twig', ['title' => $title, 'form' => $form]);
    }

    private function upload(FormInterface $form, string $field, callable $setter): void
    {
        $file = $form->has($field) ? $form->get($field)->getData() : null;
        if (!$file instanceof UploadedFile) {
            return;
        }
        $name = uniqid('', true).'.'.$file->guessExtension();
        $file->move($this->getParameter('kernel.project_dir').'/public/uploads', $name);
        $setter($name);
    }
}
