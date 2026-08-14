<?php

declare(strict_types=1);

namespace App\Shared\Application\Bus;

use App\Shared\Application\Command\CommandInterface;

interface CommandBusInterface
{
    /**
     * Dispatch a command for handling.
     *
     * Whether handling happens in-process or in a worker is a routing decision made
     * in config/packages/messenger.yaml — the caller neither knows nor cares.
     *
     * Exceptions thrown by the handler are unwrapped from Messenger's
     * HandlerFailedException before they surface here, so callers catch the real
     * domain exception (AlertNotFoundException, AlertQuotaExceededException, ...)
     * exactly as if they had called the handler directly.
     *
     * @param object[] $stamps Transport-level concerns only (DelayStamp, etc).
     *                         Never smuggle business data through a stamp.
     *
     * @throws Throwable The exception thrown by the handler, unwrapped.
     */
    public function dispatch(CommandInterface $command, array $stamps = []): void;
}
