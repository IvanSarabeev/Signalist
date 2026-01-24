<?php

namespace App\Service;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class Session
{
//    TODO: Implement a Interface - CRUD.. get, set, has, clear
    public const AUTHENTICATION_SETTINGS = 'authentication_settings';

    public function __construct(private RequestStack $requestStack)
    { }

    /**
     * @return SessionInterface
     * @throws RuntimeException
     */
    public function getSession(): SessionInterface
    {
        $session = $this->requestStack->getSession();

        if ($session instanceof SessionNotFoundException) {
            throw new RuntimeException('No active session available.');
        }

        return $session;
    }

    /**
     * Get session by key
     *
     * @param string $key - ID of Session
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $session = $this->getSession();

        if ($session->has($key)) {
            throw new RuntimeException(sprintf('Session key %s not found.', $key));
        }

        return $session->get($key);
    }

    /**
     * Get authentication settings
     *
     * @return array
     * @throws RuntimeException - User hasn't been authenticated through the system
     */
    public function getAuthenticationSettings(): array
    {
        if ($this->getSession()->has(self::AUTHENTICATION_SETTINGS)) {
            return $this->get(self::AUTHENTICATION_SETTINGS);
        }

        throw new RuntimeException('No active session available.');
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function setAuthenticationSettings(array $parameters): void
    {
        if ($parameters === []) {
            throw new InvalidArgumentException('Authentication settings cannot be empty.');
        }

        $this->getSession()->set(self::AUTHENTICATION_SETTINGS, $parameters);
    }

    public function hasAuthenticationSettings(): bool
    {
        return $this->getSession()->has(self::AUTHENTICATION_SETTINGS);
    }

    /**
     * Remove a specific session by its key
     *
     * @param string $key - Key of the Session
     * @return bool
     */
    public function remove(string $key): bool
    {
        $isRemoved = $this->getSession()->remove($key);

        if ($isRemoved !== null) {
            return true;
        }

        return false;
    }

    /**
     * Remove a list of provided session keys
     *
     * @param array $keys - An array of keys
     * @return void
     */
    public function removeMany(array $keys): void
    {
        foreach ($keys as $key) {
            if (!is_string($key) || $key === '') {
                throw new InvalidArgumentException('Each session key must be a non-empty string.');
            }

            $this->getSession()->remove($key);
        }
    }

    /**
     * Clear all sessions
     *
     * @return void
     */
    public function clear(): void
    {
        $this->getSession()->clear();
    }
}
