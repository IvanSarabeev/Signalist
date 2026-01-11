<?php

namespace App\Entity;

use App\Enum\NotificationType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class NotificationLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column]
    private int $userId;

    #[ORM\Column(enumType: NotificationType::class)]
    private NotificationType $type;

    #[ORM\Column]
    private bool $delivered = false;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deliveredAt = null;

    private DateTimeImmutable $createdAt;

    public function __construct(int $userId, NotificationType $type)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function isDelivered(): bool
    {
        return $this->delivered;
    }

    public function getDeliveredAt(): DateTimeImmutable
    {
        return $this->deliveredAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
