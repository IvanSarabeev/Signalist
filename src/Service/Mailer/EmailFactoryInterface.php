<?php

declare(strict_types=1);

namespace App\Service\Mailer;

use Symfony\Component\Mime\Email;

interface EmailFactoryInterface
{
    public function createOtpMail(string $email, string $otp): Email;

    public function createWelcomeEmail(string $email, string $name): Email;

    public function createAlertEmail(
        string  $email,
        string  $symbol,
        string  $company,
        string  $currentMetric,
        string  $threshold,
        ?string $triggeredAt,
        bool    $isUpper,
        string  $conditionSymbol,
        string  $alerType,
        string  $changePercent,
    ): Email;
}
