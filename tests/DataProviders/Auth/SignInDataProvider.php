<?php

declare(strict_types=1);

namespace App\Tests\DataProviders\Auth;

/**
 * Pure static input fixtures for sign-in scenarios.
 * No assertions, no layer-specific logic — consumed by both Controller and Service tests.
 */
final class SignInDataProvider
{
    /**
     * @return array<string, array{email: string, password: string}>
     */
    public static function validCredentials(): array
    {
        return [
            'standard user' => [
                'email'    => 'user@example.com',
                'password' => 'Secret1!',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function missingFields(): array
    {
        return [
            'missing email'    => [['password' => 'Secret1!']],
            'missing password' => [['email' => 'user@example.com']],
            'empty body'       => [[]],
        ];
    }

    /**
     * @return array<string, array{array{email: string, password: string}}>
     */
    public static function invalidEmailFormat(): array
    {
        return [
            'plain string'    => [['email' => 'notanemail',      'password' => 'Secret1!']],
            'missing at sign' => [['email' => 'userexample.com', 'password' => 'Secret1!']],
            'missing domain'  => [['email' => 'user@',           'password' => 'Secret1!']],
        ];
    }

    /**
     * @return array<string, array{array{email: string, password: string}}>
     */
    public static function passwordTooShort(): array
    {
        return [
            'five characters' => [['email' => 'user@example.com', 'password' => '12345']],
            'one character'   => [['email' => 'user@example.com', 'password' => 'x']],
            'empty password'  => [['email' => 'user@example.com', 'password' => '']],
        ];
    }
}
