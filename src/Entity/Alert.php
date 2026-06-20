<?php

namespace App\Entity;

use App\Enum\Alert\AlertCondition;
use App\Enum\Alert\AlertFrequency;
use App\Enum\Alert\AlertType;
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

    #[ORM\Column(type: Types::STRING, length: 20, enumType: AlertType::class)]
    private AlertType $alertType;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: AlertCondition::class)]
    private AlertCondition $conditionQuality;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4)]
    private ?string $thresholdValue = null;

    #[ORM\Column(type: Types::STRING, length: 30, enumType: AlertFrequency::class)]
    private AlertFrequency $frequency;

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

    public function getAlertType(): AlertType
    {
        return $this->alertType;
    }

    public function setAlertType(AlertType $alertType): static
    {
        $this->alertType = $alertType;

        return $this;
    }

    public function getConditionQuality(): AlertCondition
    {
        return $this->conditionQuality;
    }

    public function setConditionQuality(AlertCondition $conditionQuality): static
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

    public function getFrequency(): AlertFrequency
    {
        return $this->frequency;
    }

    public function setFrequency(AlertFrequency $frequency): static
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

    /**
     * Return a list of alert items with the specific stock details
     *
     * @return array<string, array{
     *     alert_name:        string|null,
     *     alert_type:        string,
     *     alert_type_label:  string,
     *     condition_quality: string,
     *     condition_label:   string,
     *     threshold_value:   float,
     *     frequency:         string,
     *     frequency_label:   string,
     *     is_active:         bool,
     *     created_at:        string|null,
     *     last_triggered_at: string|null,
     *      stock: array<string, array{
     *          symbol:         string|null,
     *          name:           string|null,
     *          currency:       string|null,
     *          price:          float,
     *          logo_url:       string|null,
     *          change_percent: string|null,
     *      }
     * }>
     */
    public function toArray(): array
    {
        return [
            'alert_name'        => $this->getAlertName(),
            'alert_type'        => $this->getAlertType()->value,
            'alert_type_label'  => $this->getAlertType()->label(),
            'condition_quality' => $this->getConditionQuality()->value,
            'condition_label'   => $this->getConditionQuality()->label(),
            'condition_symbol'  => $this->getConditionQuality()->symbol(),
            'threshold_value'   => (float) ($this->getThresholdValue() ?? 0),
            'frequency'         => $this->getFrequency()->value,
            'frequency_label'   => $this->getFrequency()->label(),
            'is_active'         => $this->isActive(),
            'created_at'        => $this->getCreatedAt()?->format('Y-m-d\TH:i:s'),
            'last_triggered_at' => $this->getLastTriggeredAt()?->format('Y-m-d\TH:i:s'),
            'stock'             => [
                'symbol'         => $this->getStock()->getSymbol(),
                'name'           => $this->getStock()->getName(),
                'currency'       => $this->getStock()->getCurrency(),
                'price'          => (float) ($this->getStock()->getCachedPrice() ?? 0),
                'logo_url'       => $this->getStock()->getLogoUrl(),
                'change_percent' => $this->getStock()->getCachedChangePercent(),
            ],
        ];
    }

    /**
     * Return a specific alert details with key stock identifier data
     *
     * @return array<string, array{
     *     alert_name:        string|null,
     *     alert_type:        string,
     *     alert_type_label:  string,
     *     condition_quality: string,
     *     condition_label:   string,
     *     threshold_value:   float,
     *     frequency:         string,
     *     frequency_label:   string,
     *     is_active:         bool,
     *     stock: array<string, array{
     *         symbol: string|null,
     *         name:   string|null,
     *     }
     * }>
     */
    public function toAlert(): array
    {
        return [
            'alert_name'        => $this->getAlertName(),
            'alert_type'        => $this->getAlertType()->value,
            'alert_type_label'  => $this->getAlertType()->label(),
            'condition_quality' => $this->getConditionQuality()->value,
            'condition_label'   => $this->getConditionQuality()->label(),
            'threshold_value'   => (float) ($this->getThresholdValue() ?? 0),
            'frequency'         => $this->getFrequency()->value,
            'frequency_label'   => $this->getFrequency()->label(),
            'is_active'         => $this->isActive(),
            'stock'             => [
                'symbol'        => $this->getStock()->getSymbol(),
                'name'          => $this->getStock()->getName(),
            ]
        ];
    }
}
