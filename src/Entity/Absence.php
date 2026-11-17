<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AbsenceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbsenceRepository::class)]
#[ApiResource(security: "is_granted('ROLE_TEACHER') or is_granted('ROLE_ADMIN')")]
class Absence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'absences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\ManyToOne(inversedBy: 'absences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotBlank]
    private \DateTimeImmutable $dateAbsence;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(length: 40)]
    private string $status = 'pending';

    public function __construct() { $this->dateAbsence = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): self { $this->student = $student; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): self { $this->course = $course; return $this; }
    public function getDateAbsence(): \DateTimeImmutable { return $this->dateAbsence; }
    public function setDateAbsence(\DateTimeImmutable $dateAbsence): self { $this->dateAbsence = $dateAbsence; return $this; }
    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): self { $this->reason = $reason; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
}
