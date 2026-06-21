<?php

declare(strict_types=1);

namespace App\Enum\Security;

enum RateLimiter: string
{
    case LOGIN = 'login';
    case LOGIN_IP = 'login_ip';
    case REGISTER = 'register';
    case OTP = 'otp';
    case GENERAL_API = 'general_api';
}
