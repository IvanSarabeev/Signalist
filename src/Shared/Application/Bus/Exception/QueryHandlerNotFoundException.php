<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus\Exception;

use App\Shared\Application\Query\QueryInterface;
use LogicException;

class QueryHandlerNotFoundException extends LogicException
{
    public static function for(QueryInterface $query): self
    {
        return new self(sprintf(
            'No handler is registered on "query.bus" for query "%s". '
            . 'Does its handler implement %s?',
            $query::class,
            'App\Shared\Application\Query\QueryHandlerInterface',
        ));
    }
}
