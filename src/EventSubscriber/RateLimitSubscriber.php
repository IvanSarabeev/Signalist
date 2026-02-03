<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Attribute\RateLimit;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final readonly class RateLimitSubscriber implements EventSubscriberInterface
{
    /** @var array<string, RateLimiterFactoryInterface> */
    private array $limiters;

    public function __construct(
        private RequestStack $requestStack,
        RateLimiterFactoryInterface $loginLimiter,
        RateLimiterFactoryInterface $loginIpLimiter,
        RateLimiterFactoryInterface $registerLimiter,
        RateLimiterFactoryInterface $generalApiLimiter,
    ) {
        $this->limiters = [
            'login' => $loginLimiter,
            'login_ip' => $loginIpLimiter,
            'register' => $registerLimiter,
            'general_api' => $generalApiLimiter,
        ];
    }


    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onController', 110],
        ];
    }

    /**
     * @param ControllerEvent $event
     * @return void
     * @throws ReflectionException
     */
    public function onController(ControllerEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $controller = $event->getController();
        if (!is_array($controller)) {
            return; // skip if not a class method
        }

        [$controllerObject, $method] = $controller;
        $reflection = new \ReflectionMethod($controllerObject, $method);

        $attributes = $reflection->getAttributes(RateLimit::class, \ReflectionAttribute::IS_INSTANCEOF);
        if (!$attributes) {
            return;
        }

        foreach ($attributes as $attribute) {
            /** @var RateLimit $rateLimit */
            $rateLimit = $attribute->newInstance();

            if (!isset($this->limiters[$rateLimit->limiter])) {
                throw new \LogicException(sprintf(
                    'Rate limiter "%s" not configured',
                    $rateLimit->limiter,
                ));
            }

            $identifier = $this->resolveIdentifier($rateLimit, $request);
            $limiter = $this->limiters[$rateLimit->limiter]->create($identifier);
            $limit = $limiter->consume();

            if (!$limit->isAccepted()) {
                throw new RateLimitExceedException(
                    $limit->getRetryAfter()->getTimestamp() !== null
                        ? max(0, $limit->getRetryAfter()->getTimestamp() - time())
                        : null
                );
            }
        }
    }

    /**
     * @param RateLimit $rateLimit
     * @param Request $request
     * @return string
     */
    private function resolveIdentifier(RateLimit $rateLimit, Request $request): string
    {
        if ($rateLimit->identifierField !== null) {
            $value = $request->request->get($rateLimit->identifierField);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        if ($rateLimit->byIpAddress) {
            return $request->getClientIp() ?? 'unknown';
        }

        return 'anonymous';
    }
}
