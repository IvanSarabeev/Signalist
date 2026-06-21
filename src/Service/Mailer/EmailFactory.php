<?php

namespace App\Service\Mailer;

use DateTime;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final readonly class EmailFactory
{
    private const FROM_MAIL = 'no-reply@signalist.com';

    public function __construct(private Environment $twig)
    { }

    /**
     * @param string $email
     * @param string $otp
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createOtpMail(string $email, string $otp): Email
    {
        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($email)
            ->subject('Your Authentication Code')
            ->html(
                $this->twig->render('emails/auth/otp.html.twig', [
                    'email' => $email,
                    'otp' => $otp
                ])
            );
    }

    /**
     * @param string $email
     * @param string $name
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createWelcomeEmail(string $email, string $name): Email
    {
        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($email)
            ->subject('Welcome')
            ->html(
                $this->twig->render('emails/auth/welcome.html.twig', [
                    'name' => $name ?: 'Guest',
                    'intro' => 'Thanks for joining Signalist. You now have the tools to track markets and make smarter moves.'
                ])
            );
    }

    /**
     * @param string $email
     * @param string $symbol
     * @param string $company
     * @param string $currentMetric
     * @param string $threshold
     * @param string|null $triggeredAt
     * @param bool $isUpper
     * @param string $conditionSymbol
     * @param string $alerType
     * @param string $changePercent
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
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
    ): Email
    {
        $template = $isUpper
            ? 'emails/alerts/stocks/stock-alert-upper.html.twig'
            : 'emails/alerts/stocks/stock-alert-lower.html.twig';

        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($email)
            ->subject('Alert')
            ->html(
                $this->twig->render($template, [
                    'timestamp'       => $triggeredAt ?? new DateTime(),
                    'symbol'          => $symbol,
                    'company'         => $company,
                    'currentPrice'    => $currentMetric,
                    'targetPrice'     => $threshold,
                    'alertType'       => $alerType,
                    'conditionSymbol' => $conditionSymbol,
                    'changePercent'   => $changePercent,
                ])
            );
    }
}
