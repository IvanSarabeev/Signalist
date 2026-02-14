<?php

namespace App\Enum;

enum NotificationType: string
{
    case USER_REGISTERED = 'user_registered';
    case LOGIN_OTP = 'login_otp';
}
