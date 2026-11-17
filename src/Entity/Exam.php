<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExamRepository::class)]
#[ApiResource(security: "is_granted('ROLE_USER')")]
class Exam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column]
    private int $duration = 60;

    #[ORM\Column]
    private float $passingScore = 10;

    #[ORM\ManyToOne(inversedBy: 'exams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\OneToMany(mappedBy: 'exam', targetEntity: Result::class, orphanRemoval: true)]
    private Collection $results;

    public function __construct() { $this->results = new ArrayCollection(); }
    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function getDuration(): int { return $this->duration; }
    public function setDuration(int $duration): self { $this->duration = $duration; return $this; }
    public function getPassingScore(): float { return $this->passingScore; }
    public function setPassingScore(float $passingScore): self { $this->passingScore = $passingScore; return $this; }
    public function getCourse(): ?Course { return $this->course; }
    public function setCourse(?Course $course): self { $this->course = $course; return $this; }
    public function getResults(): Collection { return $this->results; }
    public function __toString(): string { return $this->title; }
}
