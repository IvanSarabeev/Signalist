<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\Exception\TooManyQueryHandlersException;
use App\Shared\Application\Bus\QueryBusInterface;
use App\Shared\Application\Query\QueryInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerQueryBus implements QueryBusInterface
{
    public function __construct(private MessageBusInterface $queryBus)
    {
    }

    public function query(QueryInterface $query): mixed
    {
        try {
            $envelope = $this->queryBus->dispatch($query);
        } catch (HandlerFailedException $exception) {
            throw \App\Shared\Application\Bus\Exception\QueryHandlerNotFoundException::unwrap($exception);
        }

        $stamps = $envelope->all(HandledStamp::class);

        if ($stamps === []) {
            throw QueryHandlerNotFoundException::for($query);
        }

        if (count($stamps) > 1) {
            throw TooManyQueryHandlersException::for($query, count($stamps));
        }

        return $stamps[0]->getResult();
    }


}
