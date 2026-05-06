<?php

declare(strict_types=1);

namespace App\Tests\DataProviders\Auth;

/**
 * Pure static input fixtures for registration scenarios.
 * No assertions, no layer-specific logic — consumed by both Controller and Service tests.
 */
final class RegisterDataProvider
{
    // -------------------------------------------------------------------------
    // Valid payloads
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{
     *     fullName: string,
     *     email: string,
     *     password: string,
     *     country: string,
     *     investmentGoals: string,
     *     riskTolerance: string,
     *     preferredIndustry: string,
     * }>
     */
    public static function validPayload(): array
    {
        return [
            'complete registration' => [
                'fullName'          => 'John Doe',
                'email'             => 'john@example.com',
                'password'          => 'Secret1!',
                'country'           => 'US',
                'investmentGoals'   => 'growth',
                'riskTolerance'     => 'medium',
                'preferredIndustry' => 'technology',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Missing required fields — trigger DTO validation failures
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{array<string, string>}>
     */
    public static function missingFields(): array
    {
        $base = [
            'fullName'          => 'John Doe',
            'email'             => 'john@example.com',
            'password'          => 'Secret1!',
            'country'           => 'US',
            'investmentGoals'   => 'growth',
            'riskTolerance'     => 'medium',
            'preferredIndustry' => 'technology',
        ];

        return [
            'missing fullName'          => [array_diff_key($base, ['fullName'          => ''])],
            'missing email'             => [array_diff_key($base, ['email'             => ''])],
            'missing password'          => [array_diff_key($base, ['password'          => ''])],
            'missing country'           => [array_diff_key($base, ['country'           => ''])],
            'missing investmentGoals'   => [array_diff_key($base, ['investmentGoals'   => ''])],
            'missing riskTolerance'     => [array_diff_key($base, ['riskTolerance'     => ''])],
            'missing preferredIndustry' => [array_diff_key($base, ['preferredIndustry' => ''])],
        ];
    }

    // -------------------------------------------------------------------------
    // Invalid enum values — trigger Choice constraint failures
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{array<string, string>}>
     */
    public static function invalidEnumValues(): array
    {
        $base = [
            'fullName'          => 'John Doe',
            'email'             => 'john@example.com',
            'password'          => 'Secret1!',
            'country'           => 'US',
            'investmentGoals'   => 'growth',
            'riskTolerance'     => 'medium',
            'preferredIndustry' => 'technology',
        ];

        return [
            'invalid investmentGoals'   => [array_merge($base, ['investmentGoals'   => 'invalid_goal'])],
            'invalid riskTolerance'     => [array_merge($base, ['riskTolerance'     => 'extreme'])],
            'invalid preferredIndustry' => [array_merge($base, ['preferredIndustry' => 'crypto'])],
        ];
    }
}
