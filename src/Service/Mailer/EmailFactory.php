<?php

namespace App\Service\Mailer;

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
     * @param string $to
     * @param string $otp
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createOtpMail(string $to, string $otp): Email
    {
        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($to)
            ->subject('Your Authentication Code')
            ->html(
                $this->twig->render('emails/otp.html.twig', [
                    'otp' => $otp
                ])
            );
    }

    /**
     * @param string $to
     * @return Email
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createWelcomeEmail(string $to): Email
    {
        return (new Email())
            ->from(self::FROM_MAIL)
            ->to($to)
            ->subject('Welcome')
            ->html(
                $this->twig->render('emails/welcome.html.twig')
            );
    }
}
