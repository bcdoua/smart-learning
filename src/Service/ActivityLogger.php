<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityLogger
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
    ) {}

    public function log(string $action, ?User $user = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $log = (new ActivityLog())
            ->setAction($action)
            ->setUser($user)
            ->setContext($context)
            ->setIp($request?->getClientIp());

        $this->em->persist($log);
        $this->em->flush();
    }
}
