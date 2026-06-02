<?php

declare(strict_types=1);

namespace App\Service\Stock;

use App\Entity\Stock;
use App\Repository\StockRepository;
use App\Service\Finnhub\FinnhubService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;

final readonly class StockService implements StockServiceInterface
{
    public function __construct(
        private FinnhubService         $finnhub,
        private StockRepository        $stockRepository,
        private EntityManagerInterface $entityManager,
    )
    { }

    /**
     * Finds a stock entity by its symbol.
     *
     * @param string $symbol
     * @return Stock|null
     */
    public function findStockBySymbol(string $symbol): ?Stock
    {
        $stock = $this->stockRepository->findOneBy(['symbol' => $symbol]);

        if (!$stock) {
            return null;
        }

        return $stock;
    }

    /**
     * Finds a stock entity by its symbol or either create new Stock entity.
     *
     * @param string $symbol
     * @return Stock
     * @throws InvalidArgumentException
     */
    public function findOrCreateFromFinnhubStock(string $symbol): Stock
    {
        $stock = $this->findStockBySymbol($symbol);

        if (!$stock) {
            $profile = $this->finnhub->getCompanyProfile($symbol);

            $stock = new Stock();
            $stock->setSymbol($symbol);
            $stock->setName($profile->name);
            $stock->setExchange($profile->exchange);
            $stock->setIndustry($profile->finnhubIndustry);
            $stock->setLogoUrl($profile->logo);
            $stock->setSymbol($profile->ticker);
            $stock->setCurrency($profile->currency);
        }

        $quote = $this->finnhub->getQuote($symbol);
        $stock->setCachedPrice((string)$quote->currentPrice);
        $stock->setCachedChangePercent((string)$quote->percentChange);
        $stock->setCachedPreviousClose((string)$quote->closePriceDay);
        $stock->setCachedHigh((string)$quote->highPriceDay);
        $stock->setCachedLow((string)$quote->lowPriceDay);
        $stock->setQuoteCachedAt(new DateTimeImmutable());

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return $stock;
    }
}
