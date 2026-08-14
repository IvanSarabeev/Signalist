<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\Exception\HandlerFailedException;

/**
 * Pulls the real exception out of Messenger's HandlerFailedException wrapper.
 *
 * Without this, every domain exception arrives at the HTTP layer disguised as a
 * HandlerFailedException. An exception subscriber matching on
 * AlertNotFoundException would never fire, and every failed command would render
 * as a generic 500 — the single most common defect when introducing a bus.
 *
 * Nested buses (a command handler dispatching another command synchronously)
 * produce nested wrappers, so unwrapping recurses.
 */
final class HandlerExceptionUnwrapper
{
    public static function unwrap(HandlerFailedException $exception): Throwable
    {
        $wrapped = array_values($exception->getWrappedExceptions());

        $firstException = $wrapped[0] ?? $exception->getPrevious() ?? $exception;

        return $firstException instanceof HandlerFailedException
            ? self::unwrap($firstException)
            : $firstException;
    }
}
