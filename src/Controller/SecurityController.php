<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\SmartMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepted by Symfony Security.');
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em, SmartMailerService $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_STUDENT']);
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();

            $mailer->send($user, 'Bienvenue sur SmartLearn Academy', 'emails/welcome.html.twig');
            $this->addFlash('success', 'Compte cree. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', ['registrationForm' => $form]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('forgot_password', (string) $request->request->get('_csrf_token'))) {
            $this->addFlash('success', 'Si un compte existe, un email de reinitialisation sera envoye.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig');
    }
}
