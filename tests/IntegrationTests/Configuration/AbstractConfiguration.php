<?php

declare(strict_types=1);

namespace App\Tests\IntegrationTests\Configuration;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for functional and integration tests that require a booted Symfony kernel
 * and an HTTP client (WebTestCase).
 *
 * For pure unit tests with no kernel dependency, extend PHPUnit\Framework\TestCase directly.
 */
abstract class AbstractConfiguration extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }
}
