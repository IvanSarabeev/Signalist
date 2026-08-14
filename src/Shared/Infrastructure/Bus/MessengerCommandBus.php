<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Bus;

use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Command\CommandInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

final readonly class MessengerCommandBus implements CommandBusInterface
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    /**
     * @param object[] $stamps
     *
     * @throws Throwable
     */
    public function dispatch(CommandInterface $command, array $stamps = []): void
    {
        try {
            $this->commandBus->dispatch($command, $stamps);
        } catch (HandlerFailedException $exception) {
            throw HandlerExceptionUnwrapper::unwrap($exception);
        }
    }
}
