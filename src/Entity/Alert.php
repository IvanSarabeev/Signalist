<?php

namespace App\Entity;

use App\Repository\AlertRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AlertRepository::class)]
class Alert
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'alerts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Stock::class, inversedBy: 'alerts')]
    #[ORM\JoinColumn(name: 'stock_symbol', referencedColumnName: 'symbol', nullable: false)]
    private ?Stock $stock = null;

    #[ORM\Column(type: Types::STRING, length: 150)]
    private string $alertName;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $alertType;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $conditionQuality;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4)]
    private ?string $thresholdValue = null;

    #[ORM\Column(type: Types::STRING, length: 30)]
    private string $frequency;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $lastTriggeredAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getAlertName(): ?string
    {
        return $this->alertName;
    }

    public function setAlertName(string $alertName): static
    {
        $this->alertName = $alertName;

        return $this;
    }

    public function getAlertType(): ?string
    {
        return $this->alertType;
    }

    public function setAlertType(string $alertType): static
    {
        $this->alertType = $alertType;

        return $this;
    }

    public function getConditionQuality(): ?string
    {
        return $this->conditionQuality;
    }

    public function setConditionQuality(string $conditionQuality): static
    {
        $this->conditionQuality = $conditionQuality;

        return $this;
    }

    public function getThresholdValue(): ?string
    {
        return $this->thresholdValue;
    }

    public function setThresholdValue(string $thresholdValue): static
    {
        $this->thresholdValue = $thresholdValue;

        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(string $frequency): static
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastTriggeredAt(): ?DateTimeImmutable
    {
        return $this->lastTriggeredAt;
    }

    public function setLastTriggeredAt(?DateTimeImmutable $lastTriggeredAt): static
    {
        $this->lastTriggeredAt = $lastTriggeredAt;

        return $this;
    }
}
