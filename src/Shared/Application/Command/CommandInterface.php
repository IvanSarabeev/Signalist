<?php

declare(strict_types=1);

namespace App\Shared\Application\Command;

/**
 * Marker for a write intent.
 *
 * A command expresses something the caller *wants to happen*. It is named in the
 * imperative ("CreateAlertCommand", not "AlertCreated") and it never returns domain
 * data — if the caller needs a value back, that is a Query.
 *
 * Implementations must be immutable, serializable DTOs: scalars, enums, UIDs and
 * other value objects only. Never place a Doctrine entity or a service inside a
 * command — commands may cross a transport boundary and be rebuilt in a worker.
 */
interface CommandInterface
{
}
