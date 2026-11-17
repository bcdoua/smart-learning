<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Notification::class); }

    public function unreadFor(User $user): array
    {
        return $this->findBy(['user' => $user, 'isRead' => false], ['createdAt' => 'DESC'], 10);
    }
}
