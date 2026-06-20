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
     * @param float $currentMetric
     * @param float $threshold
     * @param string|null $triggeredAt
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createAlertUpperEmail(
        string    $email,
        string    $symbol,
        string    $company,
        float     $currentMetric,
        float     $threshold,
        ?string   $triggeredAt
    ): Email
    {
        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($email)
            ->subject('Alert')
            ->html(
                $this->twig->render('emails/alerts/stocks/stock-alert-upper.html.twig', [
                    'timestamp'    => $triggeredAt ?? new DateTime(),
                    'symbol'       => $symbol,
                    'company'      => $company,
                    'currentPrice' => $currentMetric,
                    'targetPrice'  => $threshold
                ])
            );
    }

    /**
     * @param string $email
     * @param string $symbol
     * @param string $company
     * @param float $currentMetric
     * @param float $threshold
     * @param string|null $triggeredAt
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createAlertLowerEmail(
        string    $email,
        string    $symbol,
        string    $company,
        float     $currentMetric,
        float     $threshold,
        ?string   $triggeredAt
    ): Email
    {
        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($email)
            ->subject('Alert')
            ->html(
                $this->twig->render('emails/alerts/stocks/stock-alert-lower.html.twig', [
                    'timestamp'    => $triggeredAt ?? new DateTime(),
                    'symbol'       => $symbol,
                    'company'      => $company,
                    'currentPrice' => $currentMetric,
                    'targetPrice'  => $threshold
                ])
            );
    }
}
