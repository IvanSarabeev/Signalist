<?php

namespace App\Infrastructure\Finnhub;

use App\Presentation\Http\Response\Stocks\QuoteItem;

final readonly class FinnhubQuoteMapper
{
    public function toDTO(array $quote): QuoteItem
    {
        return new QuoteItem(
            $quote['c'],
            $quote['d'],
            $quote['dp'],
            $quote['h'],
            $quote['l'],
            $quote['o'],
            $quote['pc'],
        );
    }
}
