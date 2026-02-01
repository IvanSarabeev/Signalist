<?php

namespace App\Service\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

interface BaseSessionInterface
{
    public function getSession(): SessionInterface;

    public function get(string $key);

    public function set(string $key, array $parameters = []): void;

    public function has(string $key): bool;

    public function remove(string $key): void;

    public function regenerate(): void;
}
