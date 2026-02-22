<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RateLimitExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException'
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof RateLimitExceedException) {
            return;
        }

        $response = new JsonResponse(
            ['status' => false, 'message' => 'Too many request attempts, please try again later.'],
            Response::HTTP_TOO_MANY_REQUESTS
        );

        if ($exception->getRetryAfter() !== null) {
            $response->headers->set('X-Retry-After', (string) $exception->getRetryAfter());
        }

        $event->setResponse($response);
    }
}
