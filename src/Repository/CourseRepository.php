<?php

namespace App\Repository;

use App\Entity\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Course::class); }

    public function popular(int $limit = 6): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.enrollments', 'e')
            ->addSelect('COUNT(e.id) AS HIDDEN enrollments_count')
            ->groupBy('c.id')
            ->orderBy('enrollments_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
