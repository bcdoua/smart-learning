<?php

namespace App\Repository;

use App\Entity\Result;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Result::class); }

    public function averageForStudent(User $student): float
    {
        return (float) ($this->createQueryBuilder('r')
            ->select('AVG(r.score)')
            ->andWhere('r.student = :student')
            ->setParameter('student', $student)
            ->getQuery()
            ->getSingleScalarResult() ?? 0);
    }
}
