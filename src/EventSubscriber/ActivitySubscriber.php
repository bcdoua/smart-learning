<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\ActivityLogger;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ActivitySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly ActivityLogger $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => ['onController', -128]];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/admin') && !str_starts_with($request->getPathInfo(), '/teacher') && !str_starts_with($request->getPathInfo(), '/student')) {
            return;
        }
        $user = $this->security->getUser();
        $this->logger->log('page_view', $user instanceof User ? $user : null, ['path' => $request->getPathInfo()]);
    }
}
