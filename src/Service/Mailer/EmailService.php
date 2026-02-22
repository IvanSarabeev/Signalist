<?php

namespace App\Service\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Throwable;

final readonly class EmailService
{
    private const EMAIL_PREFIX = 'Email: ';

    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) { }

    /**
     * Send Email via the Mailer Interface or Thrown an Error if something failed
     * @param Email $email
     * @return void
     * @throws TransportExceptionInterface
     * @throws Throwable
     */
    public function send(Email $email): void
    {
        try {
            $this->mailer->send($email);

            $this->logger->info(
                sprintf(self::EMAIL_PREFIX . 'send on: %s', $email->getDate()->format('Y-m-d H:i:s')), [
                    'to' => implode(', ', $email->getTo()),
                    'subject' => $email->getSubject()
                ]
            );
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error(self::EMAIL_PREFIX . 'transport failed', [
                'message' => $transportException->getMessage(),
            ]);

            throw $transportException;
        } catch (Throwable $throwable) {
            $this->logger->critical(self::EMAIL_PREFIX . 'unexpected failure', [
                'message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }
}
