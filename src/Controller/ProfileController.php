<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('profile', (string) $request->request->get('_csrf_token'))) {
            $user->setName((string) $request->request->get('name', $user->getName()));
            $user->setDarkMode($request->request->has('darkMode'));
            $em->flush();
            $this->addFlash('success', 'Profil mis a jour.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', ['user' => $user]);
    }
}
