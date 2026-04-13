<?php

namespace App\Mapper\Stock;

use App\DTO\Stock\StockResponseDTO;

final class StockProfileMapper
{
    public function toDTO(array $stockProfile): StockResponseDTO
    {
        return new StockResponseDTO(
            $stockProfile['name'],
            $stockProfile['exchange'],
            $stockProfile['country'],
            $stockProfile['currency'],
            $stockProfile['finnhubIndustry'],
            $stockProfile['logo'],
            $stockProfile['shareOutstanding'],
            $stockProfile['ticker'],
            $stockProfile['weburl'],
        );
    }
}
