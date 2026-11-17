<?php

namespace App\Entity;

use App\Repository\ActivityLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\Column(length: 120)]
    private string $action = '';

    #[ORM\Column(type: Types::JSON)]
    private array $context = [];

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ip = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
    public function getAction(): string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }
    public function getContext(): array { return $this->context; }
    public function setContext(array $context): self { $this->context = $context; return $this; }
    public function getIp(): ?string { return $this->ip; }
    public function setIp(?string $ip): self { $this->ip = $ip; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
