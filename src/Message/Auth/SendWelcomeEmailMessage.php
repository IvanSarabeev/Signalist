<?php

namespace App\Message\Auth;

final readonly class SendWelcomeEmailMessage
{
    public function __construct(public int $userId)
    { }
}
