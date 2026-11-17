<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ?HubInterface $hub = null,
    ) {}

    public function notify(User $user, string $title, string $message): Notification
    {
        $notification = (new Notification())->setUser($user)->setTitle($title)->setMessage($message);
        $this->em->persist($notification);
        $this->em->flush();

        if ($this->hub) {
            try {
                $this->hub->publish(new Update('/users/'.$user->getId().'/notifications', json_encode([
                    'title' => $title,
                    'message' => $message,
                    'createdAt' => $notification->getCreatedAt()->format(DATE_ATOM),
                ], JSON_THROW_ON_ERROR)));
            } catch (\Throwable) {
            }
        }

        return $notification;
    }
}
