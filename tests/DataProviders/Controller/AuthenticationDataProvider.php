<?php

namespace App\Tests\DataProviders\Controller;

final class AuthenticationDataProvider
{
    public static function authenticateUser(): array
    {
        return [
            'Authenticated' => [],
            'Invalid Credentials' => [],
            'Invalid JSON Payload' => [],
            'Constraint Violations' => [],
        ];
    }

    public static function registerUser(): array
    {
        return [
            'Registered' => [],
            'Invalid JSON Payload' => [],
            'Constraint Violations' => [],
        ];
    }
}
