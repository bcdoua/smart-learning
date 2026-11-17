<?php

namespace App\Repository;

use App\Entity\Absence;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AbsenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Absence::class); }

    public function countRecentForStudent(User $student, string $interval = '-30 days'): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.student = :student')
            ->andWhere('a.dateAbsence >= :since')
            ->setParameter('student', $student)
            ->setParameter('since', new \DateTimeImmutable($interval))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
