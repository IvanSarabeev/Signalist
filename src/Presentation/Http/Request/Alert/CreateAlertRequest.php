<?php

declare(strict_types=1);

namespace App\Presentation\Http\Request\Alert;

use App\Enum\Alert\AlertCondition;
use App\Enum\Alert\AlertFrequency;
use App\Enum\Alert\AlertType;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateAlertRequest
{
    private const REACHED_MAXIMUM_CHAR_LIMIT = 'Reached the maximum length of {{ limit }} characters.';

    public function __construct(
        #[Assert\NotBlank(message: 'Symbol is required.')]
        #[Assert\Type(Types::STRING)]
        #[Assert\Length(max: 5, maxMessage: self::REACHED_MAXIMUM_CHAR_LIMIT)]
        public readonly string $symbol,

        #[Assert\NotBlank(message: 'Alert name is required.')]
        #[Assert\Type(Types::STRING)]
        #[Assert\Length(max: 150, maxMessage: self::REACHED_MAXIMUM_CHAR_LIMIT)]
        public readonly string $alertName,

        #[Assert\NotBlank(message: 'Alert type is required.')]
        #[Assert\Choice(
            choices: AlertType::VALUES,
            message: 'Invalid alert type. Accepted values:: {{ choices }}.'
        )]
        public readonly string $alertType,

        #[Assert\NotBlank(message: 'Alert condition is required.')]
        #[Assert\Choice(
            choices: AlertCondition::VALUES,
            message: 'Invalid alert condition. Accepted values:: {{ choices }}.'
        )]
        public readonly string $conditionQuality,

        #[Assert\NotBlank(message: 'Alert frequency is required.')]
        #[Assert\Choice(
            choices: AlertFrequency::VALUES,
            message: 'Invalid alert frequency. Accepted values:: {{ choices }}.'
        )]
        public readonly string $frequency,

        #[Assert\NotBlank(message: 'Threshold value is required.')]
        #[Assert\Type(type: Types::DECIMAL, message: 'Threshold value must be a number.')]
        #[Assert\Positive(message: 'Threshold value must be greater than zero.')]
        public readonly string $thresholdValue,
    ) {
    }

    public function getAlertType(): AlertType
    {
        return AlertType::from($this->alertType);
    }

    public function getAlertCondition(): AlertCondition
    {
        return AlertCondition::from($this->conditionQuality);
    }

    public function getAlertFrequency(): AlertFrequency
    {
        return AlertFrequency::from($this->frequency);
    }
}
