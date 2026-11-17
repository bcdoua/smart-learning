<?php

namespace App\Message;

final readonly class RiskAlertMessage
{
    public function __construct(public int $studentId) {}
}
