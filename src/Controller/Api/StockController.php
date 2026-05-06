<?php

namespace App\Controller\Api;

use App\Presentation\Http\Request\Stock\StockListRequest;
use App\Presentation\Http\Response\ApiResponse;
use App\Service\Finnhub\FinnhubService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/stocks', name: 'api_stocks_')]
final class StockController extends AbstractController
{
    public function __construct(private readonly FinnhubService $finnhubService)
    { }

    /**
     * Symbol
     *
     * @param StockListRequest $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route(path: '', name: 'list', methods: 'GET')]
    public function list(StockListRequest $request): JsonResponse
    {
        if ($request->symbol !== null) {
            $result = $this->finnhubService->getCompanyProfile($request->symbol);
            return ApiResponse::success($result);
        }

        $result = $this->finnhubService->getPopularStocks();

        return ApiResponse::success($result);
    }

    /**
     * @param string $symbol
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route(path: '/{symbol}/company-news', name: 'news', methods: 'GET')]
    public function news(string $symbol): JsonResponse
    {
        $this->handleInvalidSymbol($symbol);

        $result = $this->finnhubService->getCompanyNews($symbol);

        if (empty($result)) {
            return ApiResponse::success([]);
        }

        return ApiResponse::success($result);
    }

    private function handleInvalidSymbol(string $symbol): void
    {
        if (!preg_match('/^[A-Z]{1,5}$/', $symbol)) {
            throw $this->createNotFoundException('Invalid symbol');
        }
    }
}
