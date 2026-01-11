<?php

namespace App\Entity;

use App\Enum\NotificationType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class UserNotificationPreference
{
    #[ORM\ManyToOne]
    private User $user;

    #[ORM\Column(enumType: NotificationType::class)]
    private NotificationType $type;

    #[ORM\Column]
    private bool $emailEnabled = true;

    #[ORM\Column]
    private bool $smsEnabled = false;
}
