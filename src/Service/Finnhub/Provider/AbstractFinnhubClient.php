<?php

namespace App\Service\Finnhub\Provider;

use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract readonly class AbstractFinnhubClient
{
    protected const GET_COMPANY_NEWS  = '/company-news';
    protected const GET_STOCK_PROFILE = '/stock/profile2';
    protected const GET_STOCK_QUOTE   = '/quote';

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
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function requestGet(string $endpoint, array $query = []): array
    {
        $query = array_merge($query, ['token' => $this->token]);

        $response = $this->httpClient->request(
            Request::METHOD_GET,
            rtrim($this->baseUrl, '/') . $endpoint,
            [
                'query' => $query,
                'timeout' => 5,
            ]
        );

        return $this->handleResponse($response);
    }

    /**
     * Handle the Response and convert it to an array.
     *
     * @param ResponseInterface $response
     * @return mixed
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode >= Response::HTTP_BAD_REQUEST) {
            throw new RuntimeException(
                sprintf('Finnhub API error (%d): %s', $statusCode, $content)
            );
        }

        if (empty($content)) {
            throw new RuntimeException(
                'Empty response from Finnhub API (invalid token or bad request)'
            );
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (isset($data['error'])) {
                throw new RuntimeException(
                    sprintf('Finnhub error: %s', $data['error'])
                );
            }

            return $data;
        } catch (JsonException $exception) {
            throw new RuntimeException(
                sprintf('Invalid JSON response: %s | %s', $content, $exception->getMessage())
            );
        }
    }
}
