<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $question = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $answer = '';

    #[ORM\Column(length: 40)]
    private string $provider = 'local';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getStudent(): ?User { return $this->student; }
    public function setStudent(?User $student): self { $this->student = $student; return $this; }
    public function getQuestion(): string { return $this->question; }
    public function setQuestion(string $question): self { $this->question = $question; return $this; }
    public function getAnswer(): string { return $this->answer; }
    public function setAnswer(string $answer): self { $this->answer = $answer; return $this; }
    public function getProvider(): string { return $this->provider; }
    public function setProvider(string $provider): self { $this->provider = $provider; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
