<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cet email est deja utilise.')]
#[ApiResource(security: "is_granted('ROLE_ADMIN')")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email = '';

    #[ORM\Column]
    private array $roles = ['ROLE_STUDENT'];

    #[ORM\Column]
    private string $password = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private bool $darkMode = false;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private bool $isBlocked = false;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $twoFactorSecret = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLogin = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'teacher', targetEntity: Course::class)]
    private Collection $courses;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Enrollment::class, orphanRemoval: true)]
    private Collection $enrollments;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Absence::class, orphanRemoval: true)]
    private Collection $absences;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Result::class, orphanRemoval: true)]
    private Collection $results;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $notifications;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->courses = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
        $this->absences = new ArrayCollection();
        $this->results = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = strtolower($email); return $this; }
    public function getUserIdentifier(): string { return $this->email; }
    public function getRoles(): array { return array_unique([...$this->roles, 'ROLE_USER']); }
    public function getRolesLabel(): string { return implode(', ', array_diff($this->getRoles(), ['ROLE_USER'])); }
    public function setRoles(array $roles): self { $this->roles = array_values(array_unique($roles)); return $this; }
    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function eraseCredentials(): void {}
    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }
    public function isDarkMode(): bool { return $this->darkMode; }
    public function setDarkMode(bool $darkMode): self { $this->darkMode = $darkMode; return $this; }
    public function isVerified(): bool { return $this->isVerified; }
    public function setIsVerified(bool $isVerified): self { $this->isVerified = $isVerified; return $this; }
    public function isBlocked(): bool { return $this->isBlocked; }
    public function setIsBlocked(bool $isBlocked): self { $this->isBlocked = $isBlocked; return $this; }
    public function getTwoFactorSecret(): ?string { return $this->twoFactorSecret; }
    public function setTwoFactorSecret(?string $twoFactorSecret): self { $this->twoFactorSecret = $twoFactorSecret; return $this; }
    public function getLastLogin(): ?\DateTimeImmutable { return $this->lastLogin; }
    public function setLastLogin(?\DateTimeImmutable $lastLogin): self { $this->lastLogin = $lastLogin; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getCourses(): Collection { return $this->courses; }
    public function getEnrollments(): Collection { return $this->enrollments; }
    public function getAbsences(): Collection { return $this->absences; }
    public function getResults(): Collection { return $this->results; }
    public function getNotifications(): Collection { return $this->notifications; }
}
