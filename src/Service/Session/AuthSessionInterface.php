<?php

namespace App\Service\Session;

interface AuthSessionInterface
{
    public function getAuthentication();

    public function setAuthentication(array $parameters = []);

    public function hasAuthentication(): bool;

    public function clearAuthentication(): void;
}
