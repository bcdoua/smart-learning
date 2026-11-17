<?php

namespace App\DataFixtures;

use App\Entity\Absence;
use App\Entity\Course;
use App\Entity\Enrollment;
use App\Entity\Exam;
use App\Entity\Notification;
use App\Entity\Result;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        $admin = $this->user('Admin SmartLearn', 'admin@smartlearn.test', ['ROLE_ADMIN']);
        $teacher = $this->user('Sarah Teacher', 'teacher@smartlearn.test', ['ROLE_TEACHER']);
        $student = $this->user('Israa Student', 'student@smartlearn.test', ['ROLE_STUDENT']);
        foreach ([$admin, $teacher, $student] as $user) {
            $user->setPassword($this->hasher->hashPassword($user, 'Password123!'))->setIsVerified(true);
            $manager->persist($user);
        }

        $titles = ['Symfony 7 Professionnel', 'Data Science avec Python', 'Design UI SaaS', 'Cybersecurite Web'];
        $courses = [];
        foreach ($titles as $i => $title) {
            $course = (new Course())
                ->setTitle($title)
                ->setDescription('Cours complet avec videos, PDF, exercices, examens et accompagnement IA pour progresser rapidement.')
                ->setLevel(['Beginner', 'Intermediate', 'Advanced', 'Intermediate'][$i])
                ->setCategory(['Backend', 'Data', 'Design', 'Security'][$i])
                ->setTeacher($teacher);
            $courses[] = $course;
            $manager->persist($course);

            $enrollment = (new Enrollment())->setStudent($student)->setCourse($course)->setProgress(random_int(25, 95));
            $manager->persist($enrollment);

            $exam = (new Exam())->setTitle('Examen '.$title)->setDuration(60)->setPassingScore(10)->setCourse($course);
            $manager->persist($exam);

            $manager->persist((new Result())->setStudent($student)->setExam($exam)->setScore(random_int(8, 18))->setStatus('published'));
        }

        $manager->persist((new Absence())->setStudent($student)->setCourse($courses[0])->setStatus('unjustified')->setReason('Absent'));
        $manager->persist((new Notification())->setUser($student)->setTitle('Bienvenue')->setMessage('Votre espace SmartLearn Academy est pret.'));

        $manager->flush();
    }

    private function user(string $name, string $email, array $roles): User
    {
        return (new User())->setName($name)->setEmail($email)->setRoles($roles);
    }
}
