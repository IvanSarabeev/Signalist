<?php

namespace App\Entity;

use App\Enum\Billing\Plan;
use App\Enum\Billing\SubscriptionStatus;
use App\Repository\SubscriptionRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionRepository::class)]
#[ORM\Table(name: 'subscription')]
#[ORM\UniqueConstraint(name: 'UNIQ_SUBSCRIPTION_STRIPE_ID', fields: ['stripeSubscriptionId'])]
#[ORM\Index(name: 'IDX_SUBSCRIPTION_STATUS', fields: ['status'])]
#[ORM\HasLifecycleCallbacks]
class Subscription
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'subscription')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(type: Types::STRING, length: 64)]
    private ?string $stripeCustomerId = null;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: Plan::class)]
    private Plan $plan;

    #[ORM\Column(type: Types::STRING, length: 10, enumType: SubscriptionStatus::class)]
    private SubscriptionStatus $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $currentPeriodEnd = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $cancelAtPeriodEnd = false;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true, enumType: Plan::class)]
    private ?Plan $pendingPlan = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $pendingPlanEffectiveAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $syncedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function grantsAccess(): bool
    {
        return $this->status->grantsAccess();
    }

    public function effectivePlan(): Plan
    {
        return $this->grantsAccess() ? $this->plan : Plan::OPEN;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStripeSubscriptionId(): ?string
    {
        return $this->stripeSubscriptionId;
    }

    public function setStripeSubscriptionId(string $stripeSubscriptionId): static
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;

        return $this;
    }

    public function getStripeCustomerId(): ?string
    {
        return $this->stripeCustomerId;
    }

    public function setStripeCustomerId(string $stripeCustomerId): static
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }

    public function getPlan(): ?Plan
    {
        return $this->plan;
    }

    public function setPlan(Plan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getStatus(): ?SubscriptionStatus
    {
        return $this->status;
    }

    public function setStatus(SubscriptionStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCurrentPeriodEnd(): ?DateTimeImmutable
    {
        return $this->currentPeriodEnd;
    }

    public function setCurrentPeriodEnd(?DateTimeImmutable $currentPeriodEnd): static
    {
        $this->currentPeriodEnd = $currentPeriodEnd;

        return $this;
    }

    public function isCancelAtPeriodEnd(): ?bool
    {
        return $this->cancelAtPeriodEnd;
    }

    public function setCancelAtPeriodEnd(bool $cancelAtPeriodEnd): static
    {
        $this->cancelAtPeriodEnd = $cancelAtPeriodEnd;

        return $this;
    }

    public function getPendingPlan(): ?string
    {
        return $this->pendingPlan;
    }

    public function setPendingPlan(?string $pendingPlan): static
    {
        $this->pendingPlan = $pendingPlan;

        return $this;
    }

    public function getPendingPlanEffectiveAt(): ?DateTimeImmutable
    {
        return $this->pendingPlanEffectiveAt;
    }

    public function setPendingPlanEffectiveAt(?DateTimeImmutable $pendingPlanEffectiveAt): static
    {
        $this->pendingPlanEffectiveAt = $pendingPlanEffectiveAt;

        return $this;
    }

    public function getSyncedAt(): ?DateTimeImmutable
    {
        return $this->syncedAt;
    }

    public function setSyncedAt(?DateTimeImmutable $syncedAt): static
    {
        $this->syncedAt = $syncedAt;

        return $this;
    }

    public function isStalerThan(DateTimeImmutable $eventTime): bool
    {
        return $this->syncedAt === null || $this->syncedAt < $eventTime;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

}
