<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ApiResource(security: "is_granted('ROLE_USER')")]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 40)]
    private string $status = 'active';

    #[ORM\Column]
    private int $progress = 0;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): self { $this->student = $student; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): self { $this->course = $course; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getProgress(): int { return $this->progress; }
    public function setProgress(int $progress): self { $this->progress = max(0, min(100, $progress)); return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
