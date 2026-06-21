<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;

interface EmailServiceInterface
{
    /** @throws TransportExceptionInterface */
    public function send(Email $email): void;
}
