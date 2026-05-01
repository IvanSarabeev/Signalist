<?php

declare(strict_types=1);

namespace App\Tests\DataProviders\Controller;

final class AbstractControllerDataProvider
{
    // -------------------------------------------------------------------------
    // createPaginationParametersFromRequest
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{page: int, limit: int, expectedOffset: int, expectedFetchLimit: int}>
     */
    public static function validPaginationParameters(): array
    {
        return [
            'first page default limit'  => ['page' => 1,  'limit' => 10, 'expectedOffset' => 0,   'expectedFetchLimit' => 10],
            'second page default limit' => ['page' => 2,  'limit' => 10, 'expectedOffset' => 10,  'expectedFetchLimit' => 10],
            'first page custom limit'   => ['page' => 1,  'limit' => 25, 'expectedOffset' => 0,   'expectedFetchLimit' => 25],
            'third page limit of 5'     => ['page' => 3,  'limit' => 5,  'expectedOffset' => 10,  'expectedFetchLimit' => 5],
            'large page number'         => ['page' => 100,'limit' => 50, 'expectedOffset' => 4950,'expectedFetchLimit' => 50],
        ];
    }

    /**
     * @return array<string, array{page: int, limit: int}>
     */
    public static function invalidPaginationParameters(): array
    {
        return [
            'page zero'         => ['page' => 0,  'limit' => 10],
            'limit zero'        => ['page' => 1,  'limit' => 0],
            'both zero'         => ['page' => 0,  'limit' => 0],
            'negative page'     => ['page' => -1, 'limit' => 10],
            'negative limit'    => ['page' => 1,  'limit' => -5],
            'both negative'     => ['page' => -3, 'limit' => -3],
        ];
    }

    // -------------------------------------------------------------------------
    // constraintViolationJsonResponse
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{violations: array<array{path: string, message: string}>, formatMessages: bool, formatToArray: bool, statusCode: int}>
     */
    public static function singleViolation(): array
    {
        return [
            'email field invalid' => [
                'violations'     => [['path' => 'email',    'message' => 'This value is not a valid email address.']],
                'formatMessages' => false,
                'formatToArray'  => false,
                'statusCode'     => 400,
            ],
            'password too short' => [
                'violations'     => [['path' => 'password', 'message' => 'This value is too short.']],
                'formatMessages' => false,
                'formatToArray'  => false,
                'statusCode'     => 422,
            ],
        ];
    }

    /**
     * @return array<string, array{violations: array<array{path: string, message: string}>, formatMessages: bool, formatToArray: bool}>
     */
    public static function multipleViolations(): array
    {
        return [
            'two fields, plain format' => [
                'violations'     => [
                    ['path' => 'email',    'message' => 'This value is not a valid email address.'],
                    ['path' => 'password', 'message' => 'This value is too short.'],
                ],
                'formatMessages' => false,
                'formatToArray'  => false,
            ],
            'two fields, html format' => [
                'violations'     => [
                    ['path' => 'email',    'message' => 'This value is not a valid email address.'],
                    ['path' => 'password', 'message' => 'This value is too short.'],
                ],
                'formatMessages' => true,
                'formatToArray'  => false,
            ],
            'array-style paths' => [
                'violations'     => [
                    ['path' => '[0]email',    'message' => 'Required field missing.'],
                    ['path' => '[1]password', 'message' => 'Value too short.'],
                ],
                'formatMessages' => false,
                'formatToArray'  => true,
            ],
        ];
    }

    /**
     * @return array<string, array{violations: array<array{path: string, message: string}>}>
     */
    public static function duplicateMessages(): array
    {
        return [
            'same message on two fields' => [
                'violations' => [
                    ['path' => 'email',    'message' => 'This value should not be blank.'],
                    ['path' => 'password', 'message' => 'This value should not be blank.'],
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // normalizeEnumFields
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{propertyValue: string, expectedValue: string}>
     */
    public static function validEnumLabels(): array
    {
        return [
            'lowercase label'  => ['propertyValue' => 'active',   'expectedValue' => 'active_value'],
            'uppercase label'  => ['propertyValue' => 'INACTIVE',  'expectedValue' => 'inactive_value'],
            'mixed case label' => ['propertyValue' => 'Pending',   'expectedValue' => 'pending_value'],
        ];
    }

    /**
     * @return array<string, array{propertyValue: mixed}>
     */
    public static function skippedEnumValues(): array
    {
        return [
            'empty string'    => ['propertyValue' => ''],
            'integer value'   => ['propertyValue' => 42],
            'null value'      => ['propertyValue' => null],
            'boolean value'   => ['propertyValue' => true],
        ];
    }
}
