<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProcessedStripeEventRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessedStripeEventRepository::class)]
#[ORM\Table(name: 'processed_stripe_event')]
#[ORM\UniqueConstraint(name: 'UNIQ_PROCESSED_STRIPE_EVENT_ID', fields: ['stripeEventId'])]
#[ORM\Index(name: 'IDX_PROCESSED_STRIPE_EVENT_AT', fields: ['processedAt'])]
class ProcessedStripeEvent
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $stripeEventId;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $type;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $processedAt;

    /**
     * @param string $stripeEventId
     * @param string $type
     */
    public function __construct(string $stripeEventId, string $type)
    {
        $this->stripeEventId = $stripeEventId;
        $this->type          = $type;
        $this->processedAt   = new DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStripeEventId(): ?string
    {
        return $this->stripeEventId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }
}
