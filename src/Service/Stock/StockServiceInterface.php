<?php

declare(strict_types=1);

namespace App\Service\Stock;

use App\Entity\Stock;

interface StockServiceInterface
{
    public function findStockBySymbol(string $symbol): ?Stock;

    public function findOrCreateFromFinnhubStock(string $symbol): Stock;
}
