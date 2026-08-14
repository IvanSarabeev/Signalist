<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus\Exception;

use App\Shared\Application\Query\QueryInterface;
use LogicException;

class TooManyQueryHandlersException extends LogicException
{
    public static function for(QueryInterface $query, int $handlerCount): self
    {
        return new self(sprintf(
            'Query "%s" was handled by %d handlers on "query.bus"; exactly one is required.',
            $query::class,
            $handlerCount,
        ));
    }

}
