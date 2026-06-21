<?php

declare(strict_types=1);

namespace App\Service\Alert;

use App\Entity\Alert;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AlertTriggerService implements AlertTriggerServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    { }

    /**
     * Stamp the alert as triggered and persist the change.
     *
     * For ONCE frequency, isActive is also set to false so the alert is
     * never re-evaluated by the scheduler.
     */
    public function handleTrigger(Alert $alert): void
    {
        $alert->setLastTriggeredAt(new DateTimeImmutable());

        if ($alert->getFrequency()->deactivatesAfterTrigger()) {
            $alert->setIsActive(false);
        }

        $this->entityManager->flush();
    }
}
