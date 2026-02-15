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
}
