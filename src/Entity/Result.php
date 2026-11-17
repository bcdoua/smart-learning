<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ApiResource(security: "is_granted('ROLE_USER')")]
class Result
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\ManyToOne(inversedBy: 'results')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Exam $exam = null;

    #[ORM\Column]
    private float $score = 0;

    #[ORM\Column(length: 40)]
    private string $status = 'pending';

    public function getId(): ?int { return $this->id; }
    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): self { $this->student = $student; return $this; }
    public function getExam(): ?Exam { return $this->exam; }
    public function setExam(?Exam $exam): self { $this->exam = $exam; return $this; }
    public function getScore(): float { return $this->score; }
    public function setScore(float $score): self { $this->score = $score; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
}
