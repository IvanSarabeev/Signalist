<?php

namespace App\MessageHandler;

use App\Message\EmailNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class EmailNotificationHandler
{

    public function __construct(private MailerInterface $mailer)
    {
    }

    public function __invoke(EmailNotification $notification)
    {
        echo 'Creating a PDF contract note...<br>';
        /**
         * 1. Create a PDF contract note
         * 2. Email the contract note to the buyer
         */
        echo 'Creating a email contract note to ' . $notification->getOrder()->getBuyer()->getEmail() . '<br>';

        $email = (new Email())
            ->from('sales@stackapp.com')
            ->to($notification->getOrder()->getBuyer()->getEmail())
            ->subject('Contract note for order ' . $notification->getOrder()->getId())
            ->text('Here is your contract note');

        $this->mailer->send($email);
    }
}
