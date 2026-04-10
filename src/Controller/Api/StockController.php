<?php

namespace App\Controller\Api;

use App\Service\Finnhub\FinnhubService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/stocks', name: 'api_stocks_')]
final class StockController extends AbstractController
{
    public function __construct(private readonly FinnhubService $finnhubService)
    { }

    /**
     * Symbol
     *
     * @param string $symbol
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route(path: '/{symbol}/profile', name: 'profile', methods: 'GET')]
    public function profile(string $symbol): JsonResponse
    {
        $this->handleInvalidSymbol($symbol);

        $result = $this->finnhubService->getCompanyProfile($symbol);

        if (empty($result)) {
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        return $this->json($result);
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
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        return $this->json($result);
    }

    private function handleInvalidSymbol(string $symbol): void
    {
        if (!preg_match('/^[A-Z]{1,5}$/', $symbol)) {
            throw $this->createNotFoundException('Invalid symbol');
        }
    }
}
