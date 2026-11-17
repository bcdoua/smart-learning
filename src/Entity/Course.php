<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ApiResource(security: "is_granted('ROLE_USER')")]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $description = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdf = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 60)]
    private string $level = 'Beginner';

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $category = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'courses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $teacher = null;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollment::class, orphanRemoval: true)]
    private Collection $enrollments;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Absence::class, orphanRemoval: true)]
    private Collection $absences;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Exam::class, orphanRemoval: true)]
    private Collection $exams;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->enrollments = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->exams = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function getVideo(): ?string { return $this->video; }
    public function setVideo(?string $video): self { $this->video = $video; return $this; }
    public function getPdf(): ?string { return $this->pdf; }
    public function setPdf(?string $pdf): self { $this->pdf = $pdf; return $this; }
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
    public function getLevel(): string { return $this->level; }
    public function setLevel(string $level): self { $this->level = $level; return $this; }
    public function getCategory(): ?string { return $this->category; }
    public function setCategory(?string $category): self { $this->category = $category; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getTeacher(): ?User { return $this->teacher; }
    public function setTeacher(?User $teacher): self { $this->teacher = $teacher; return $this; }
    public function getEnrollments(): Collection { return $this->enrollments; }
    public function getAbsences(): Collection { return $this->absences; }
    public function getExams(): Collection { return $this->exams; }
    public function __toString(): string { return $this->title; }
}
