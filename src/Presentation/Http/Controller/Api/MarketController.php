<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Api;

use App\Presentation\Http\Response\ApiResponse;
use App\Service\Market\MarketOverviewServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('Market')]
#[Route(path: '/api/v1/markets', name: 'api_markets_')]
final readonly class MarketController
{
    public function __construct(private MarketOverviewServiceInterface $marketOverviewService)
    { }

    /**
     * Index quotes for MarketOverviewPanel (S&P 500, Nasdaq, Dow, etc).
     *
     * @return JsonResponse
     */
    #[Route(path: '/overview', name: 'overview', methods: ['GET'])]
    public function overview(): JsonResponse
    {
        $result = $this->marketOverviewService->getIndexQuotes();

        return ApiResponse::success($result);
    }

    /**
     * Get trending market news
     *
     * @param int $limit
     * @return JsonResponse
     */
    #[Route(path: "/trending", name: 'trending_', methods: ['GET'])]
    public function trending(#[MapQueryParameter] int $limit = 5): JsonResponse
    {
        $result = $this->marketOverviewService->getTrendingCompanies($limit);

        return ApiResponse::success($result);
    }

    /**
     * Upcoming earnings for UpcomingEarningsPanel — Finnhub's
     * /calendar/earnings endpoint, free tier. Defaults to "today through
     * +14 days" if no range is given.
     *
     * @param string|null $from ISO date, e.g. 2026-07-12
     * @param string|null $to ISO date, e.g. 2026-07-26
     * @param string|null $symbol
     * @return JsonResponse
     */
    #[Route(path: '/earnings-calendar', name: 'earnings_calendar', methods: ['GET'])]
    public function earningsCalendar(
        #[MapQueryParameter] ?string $from = null,
        #[MapQueryParameter] ?string $to = null,
        #[MapQueryParameter] ?string $symbol = null,
    ): JsonResponse
    {
        $result = $this->marketOverviewService->getUpcomingEarnings($from, $to, $symbol);

        return ApiResponse::success($result);
    }
}
