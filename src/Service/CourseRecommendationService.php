<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CourseRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class CourseRecommendationService
{
    public function __construct(private readonly CourseRepository $courses) {}

    /**
     * Returns scored course recommendations for a student.
     * Accepts UserInterface to be safely called from controllers.
     */
    public function forStudent(UserInterface $student, int $limit = 6): array
    {
        if (!$student instanceof User) {
            return [];
        }

        $enrolledCategories = [];
        foreach ($student->getEnrollments() as $enrollment) {
            $category = $enrollment->getCourse()?->getCategory();
            if ($category) {
                $enrolledCategories[$category] = true;
            }
        }

        $scored = [];
        foreach ($this->courses->findAll() as $course) {
            $enrollmentCount = $course->getEnrollments()->count();
            $popularity = $enrollmentCount * 20;

            $engagement = 0;
            foreach ($course->getEnrollments() as $enrollment) {
                $engagement += $enrollment->getProgress();
            }
            $engagement = $enrollmentCount > 0 ? $engagement / $enrollmentCount : 0;

            $interest = isset($enrolledCategories[$course->getCategory()]) ? 35 : 0;

            $scored[] = ['course' => $course, 'score' => $popularity + $engagement + $interest];
        }

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }
}
