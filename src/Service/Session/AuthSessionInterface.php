<?php

namespace App\Service\Session;

interface AuthSessionInterface
{
    public const AUTHENTICATION_KEY = 'authentication_settings';

    public function getAuthentication();

    public function setAuthentication(array $parameters = []);

    public function hasAuthentication(): bool;

    public function clearAuthentication(): void;
}
