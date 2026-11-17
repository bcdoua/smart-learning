<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class ChatbotRequest
{
    #[Assert\NotBlank]
    public string $question = '';

    public ?int $courseId = null;
}
