<?php

declare(strict_types=1);

namespace App\Service\Finhub\Provider;

class FinhubClient extends AbstractFinhubClient
{
    private const GET_COMPANY_NEWS = '/company-news';
    private const GET_STOCK = '/stock';
    private const GET_SEARCH = '/search';

    public const GET_ENDPOINTS = [
        self::GET_COMPANY_NEWS,
        self::GET_STOCK,
        self::GET_SEARCH,
    ];

}
