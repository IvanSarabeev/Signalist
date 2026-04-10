<?php

namespace App\Service\Finhub\Provider;

use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract readonly class AbstractFinnhubClient
{
    protected const GET_COMPANY_NEWS = '/company-news';
    protected const GET_STOCK_PROFILE = '/stock/profile2';

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected string              $baseUrl,
        protected string              $token,
    )
    { }

    /**
     * Make an GET request to Finnhub API
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function requestGet(string $endpoint, array $query = []): array
    {
        $query['token'] = $this->token;

        $response = $this->httpClient->request(
            Request::METHOD_GET,
            rtrim($this->baseUrl, '/') . $endpoint,
            ['query' => $query]
        );

        return $this->handleResponse($response);
    }

    /**
     * Handle the Response and convert it to an array.
     *
     * @param ResponseInterface $response
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= Response::HTTP_BAD_REQUEST) {
            throw new RuntimeException(
                sprintf('Finnhub API error: %s', $response->getContent(false))
            );
        }

        return $response->toArray(false);
    }
}
