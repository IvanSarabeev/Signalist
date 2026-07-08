<?php

declare(strict_types=1);

namespace App\Service\Alert\Metric;

use App\Entity\Alert;
use App\Enum\Alert\AlertType;
use App\Infrastructure\Finnhub\FinnhubQuoteMapper;
use App\Presentation\Http\Exception\Services\Alert\UnsupportedAlertTypeException;
use App\Service\Finnhub\Provider\FinnhubClientInterface;

final readonly class AlertMetricProvider implements AlertMetricProviderInterface
{
    public function __construct(
        private FinnhubQuoteMapper     $quoteMapper,
        private FinnhubClientInterface $finnhubClient,
    )
    { }

    /**
     * Fetch the live metric from Finnhub's /quote endpoint.
     *
     * Finnhub /quote response fields used here:
     *   c  — current price
     *   dp — daily percentage change
     *
     * Types that require a dedicated endpoint (e.g. /stock/candle for MA/RSI,
     * or a different quote field for volume) are not implemented yet and throw.
     *
     * @throws UnsupportedAlertTypeException
     */
    public function getCurrentMetric(Alert $alert): float
    {
        $quote = $this->quoteMapper->toDTO(
            $this->finnhubClient->getQuote($alert->getStock()->getSymbol())
        );

        return match ($alert->getAlertType()) {
            AlertType::PRICE          => $quote->currentPrice,
            AlertType::PERCENT_CHANGE => $quote->percentChange,

            // The following types require additional Finnhub endpoints that are
            // not yet wired up. Add implementations here as you expand the service.
            default => throw new UnsupportedAlertTypeException($alert->getAlertType()),
        };
    }
}
