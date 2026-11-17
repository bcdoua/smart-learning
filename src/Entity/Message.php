<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(security: "is_granted('ROLE_USER')")]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\ManyToOne]
    private ?User $receiver = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $file = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $room = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct() { $this->createdAt = new \DateTimeImmutable(); }
    public function getId(): ?int { return $this->id; }
    public function getSender(): ?User { return $this->sender; }
    public function setSender(?User $sender): self { $this->sender = $sender; return $this; }
    public function getReceiver(): ?User { return $this->receiver; }
    public function setReceiver(?User $receiver): self { $this->receiver = $receiver; return $this; }
    public function getContent(): string { return $this->content; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function getFile(): ?string { return $this->file; }
    public function setFile(?string $file): self { $this->file = $file; return $this; }
    public function getRoom(): ?string { return $this->room; }
    public function setRoom(?string $room): self { $this->room = $room; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
