<?php

declare(strict_types=1);

namespace App\Enum;

final class RateLimiterTypes
{
    public const LOGIN = 'login';
    public const LOGIN_IP = 'login_ip';
    public const REGISTER = 'register';
    public const GENERAL_API = 'general_api';
}
