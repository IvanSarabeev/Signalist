<?php

namespace App\Presentation\Http\Controller\Api;

use App\Enum\Finnhub\CategoryNews;
use App\Infrastructure\Routing\RouteRequirements;
use App\Presentation\Http\Request\Stock\StockListRequest;
use App\Presentation\Http\Response\ApiResponse;
use App\Service\Finnhub\FinnhubServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/stocks', name: 'api_stocks_')]
#[OA\Tag(name: 'Stocks')]
final class StockController extends AbstractController
{
    public function __construct(private readonly FinnhubServiceInterface $finnhubService)
    { }

    /**
     * Symbol
     *
     * @param StockListRequest $request
     * @return JsonResponse
     */
    #[Route(path: '', name: 'list', methods: ['GET'])]
    public function list(#[ValueResolver('stock_list_request')] StockListRequest $request): JsonResponse
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
     */
    #[Route(path: '/{symbol}/company-news', name: 'company_news', requirements: ['symbol' => RouteRequirements::SYMBOL_REGEX], methods: ['GET'])]
    public function companyNews(string $symbol): JsonResponse
    {
        $result = $this->finnhubService->getCompanyNews($symbol);

        if (empty($result)) {
            return ApiResponse::success(status: Response::HTTP_NO_CONTENT);
        }

        return ApiResponse::success($result);
    }

    #[Route(path: '/{category}/news', name: 'news', methods: ['GET'])]
    public function news(
        CategoryNews $category,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $limit = 10,
    ): JsonResponse
    {
        $result = $this->finnhubService->getMarketNews($category, $page, $limit);

        return ApiResponse::success(data: $result->items, meta: $result->meta());
    }
}
