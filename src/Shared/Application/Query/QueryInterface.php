<?php

declare(strict_types=1);

namespace App\Shared\Application\Query;

/**
 * Marker for a read intent.
 *
 * The template parameter carries the shape the query resolves to, which lets
 * PHPStan infer the return type of QueryBusInterface::ask() at every call site.
 * Annotate each query with `@implements QueryInterface<TheViewDto>` and calls to
 * ask() are then typed without any manual assertion:
 *
 *     $view = $this->queryBus->ask(new ListAlertsQuery($userId));  // AlertListView
 *
 * Queries must be side-effect free and are always handled synchronously.
 *
 * @template-covariant TResult
 */
interface QueryInterface
{
}
