<?php

namespace App\Mapper\Stock;

use App\DTO\Stock\QuoteResponseDTO;

final class QuoteMapper
{
    public function toDTO(array $quote): QuoteResponseDTO
    {
        return new QuoteResponseDTO(
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
