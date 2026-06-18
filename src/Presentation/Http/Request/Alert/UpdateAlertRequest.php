<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Alert;

use App\Enum\Alert\AlertCondition;
use App\Enum\Alert\AlertFrequency;
use App\Enum\Alert\AlertType;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateAlertRequest
{
    private const REACHED_MAXIMUM_CHAR_LIMIT = 'Reached the maximum length of {{ limit }} characters.';

    public function __construct(
        #[Assert\Type(Types::STRING)]
        #[Assert\Length(max: 150, maxMessage: self::REACHED_MAXIMUM_CHAR_LIMIT)]
        public ?string $alertName = null,

        #[Assert\Choice(
            choices: AlertType::VALUES,
            message: 'Invalid alert type. Accepted values: {{ choices }}.'
        )]
        public ?string $alertType = null,

        #[Assert\Choice(
            choices: AlertCondition::VALUES,
            message: 'Invalid alert condition. Accepted values: {{ choices }}.'
        )]
        public ?string $conditionQuality = null,

        #[Assert\Choice(
            choices: AlertFrequency::VALUES,
            message: 'Invalid frequency. Accepted values: {{ choices }}.'
        )]
        public ?string $frequency = null,

        #[Assert\Type(Types::DECIMAL, message: 'Threshold value must be a number.')]
        #[Assert\Positive(message: 'Threshold value must be greater than zero.')]
        public ?string $thresholdValue = null,

        #[Assert\Type(Types::BOOLEAN, message: 'isActive must be a boolean.')]
        public ?bool $isActive = null,
    )
    { }

    public function isEmpty(): bool
    {
        return $this->alertName === null
            && $this->alertType === null
            && $this->conditionQuality === null
            && $this->frequency === null
            && $this->thresholdValue === null
            && $this->isActive === null;
    }

    public function getAlertType(): ?AlertType
    {
        return $this->alertType !== null ? AlertType::from($this->alertType) : null;
    }

    public function getAlertConditionQuality(): ?AlertCondition
    {
        return $this->conditionQuality !== null ? AlertCondition::from($this->conditionQuality) : null;
    }

    public function getAlertFrequency(): ?AlertFrequency
    {
        return $this->frequency !== null ? AlertFrequency::from($this->frequency) : null;
    }
}
