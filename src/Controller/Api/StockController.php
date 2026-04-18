<?php

namespace App\Controller\Api;

use App\Response\ApiResponse;
use App\Service\Finnhub\FinnhubService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/stocks', name: 'api_stocks_')]
final class StockController extends AbstractController
{
    public function __construct(private readonly FinnhubService $finnhubService)
    { }

    /**
     * Symbol
     *
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route(path: '', name: 'list', methods: 'GET')]
    public function list(Request $request): JsonResponse
    {
        $query = trim($request->query->getString('symbol'));

        if ($query !== '') {
            $this->handleInvalidSymbol($query);
            $result = $this->finnhubService->getCompanyProfile($query);
        } else {
            $result = $this->finnhubService->getPopularStocks();
        }

        if (empty($result)) {
            return ApiResponse::success([]);
        }

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
