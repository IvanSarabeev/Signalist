<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use App\Shared\Application\Query\QueryInterface;
use App\Shared\Application\QueryHandlerNotFoundException;
use App\Shared\Application\Throwable;
use App\Shared\Application\TooManyQueryHandlersException;

interface QueryBusInterface
{
    /**
     * Resolve a query to its result.
     *
     * @template TResult
     *
     * @param QueryInterface<TResult> $query
     *
     * @return TResult
     *
     * @throws QueryHandlerNotFoundException  No handler is registered for the query.
     * @throws TooManyQueryHandlersException  More than one handler matched; the
     *                                        result would be ambiguous.
     * @throws Throwable                      The exception thrown by the handler, unwrapped.
     */
    public function query(): mixed;
}
