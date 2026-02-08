<?php

namespace App\Service\Session;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final readonly class Session implements BaseSessionInterface, AuthSessionInterface
{
    private const AUTHENTICATION_KEY = 'authentication_settings';

    public function __construct(private RequestStack $requestStack)
    { }

    /**
     * @return SessionInterface
     * @throws RuntimeException
     */
    public function getSession(): SessionInterface
    {
        try {
            return $this->requestStack->getSession();
        } catch (SessionNotFoundException $exception) {
            throw new RuntimeException('No active session found.' . PHP_EOL . $exception->getMessage());
        }
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

        if (!$session->has($key)) {
            throw new RuntimeException(sprintf('Session key %s not found.', $key));
        }

        return $session->get($key);
    }

    public function set(string $key, array $parameters = []): void
    {
        if (empty($key) || empty($parameters)) {
            throw new InvalidArgumentException('Session parameters are empty.');
        }

        $session = $this->getSession();
        $session->set($key, $parameters);
    }

    public function has(string $key): bool
    {
        return $this->getSession()->has($key);
    }

    /**
     * Remove a specific session by its key
     *
     * @param string $key - Key of the Session
     * @return void
     */
    public function remove(string $key): void
    {
       $this->getSession()->remove($key);
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

    public function regenerate(): void
    {
        $this->getSession()->migrate(true);
    }

    /**
     * Get authentication settings
     *
     * @return array
     * @throws RuntimeException - User hasn't been authenticated through the system
     */
    public function getAuthentication(): array
    {
        return $this->getSession()->get(self::AUTHENTICATION_KEY);
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function setAuthentication(array $parameters = []): void
    {
        if (empty($parameters)) {
            throw new InvalidArgumentException('Authentication settings cannot be empty.');
        }

        $this->getSession()->set(self::AUTHENTICATION_KEY, $parameters);
    }

    public function hasAuthentication(): bool
    {
        return $this->getSession()->has(self::AUTHENTICATION_KEY);
    }

    public function clearAuthentication(): void
    {
        $this->remove(self::AUTHENTICATION_KEY);
    }
}
