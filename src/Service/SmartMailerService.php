<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SmartMailerService
{
    public function __construct(private readonly MailerInterface $mailer) {}

    public function send(User $user, string $subject, string $template, array $context = []): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@smartlearn.local', 'SmartLearn Academy'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context(['user' => $user] + $context);

        $this->mailer->send($email);
    }
}
