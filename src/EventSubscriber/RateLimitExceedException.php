<?php

namespace App\EventSubscriber;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RateLimitExceedException extends RuntimeException
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof RateLimitExceedException) {
            return;
        }

        $response = new JsonResponse(
            [
                'status' => false,
                'message' => $exception->getMessage(),
            ],
            Response::HTTP_TOO_MANY_REQUESTS
        );

//        if ($exception->getRetryAfter() !== null) {
//            $response->headers->set(
//                'Retry-After',
//                (string) $exception->getRetryAfter()
//            );
//        }

        $event->setResponse($response);
    }
}
