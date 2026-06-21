<?php

declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use App\Presentation\Http\Exception\RequestValidationException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final class RequestValidationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof RequestValidationException) {
            return;
        }

        $event->setResponse(
            new JsonResponse(
                data: [
                    'status'  => false,
                    'message' => $exception->getMessage(),
                    'errors'  => $exception->getErrors(),
                ],
                status: $exception->getStatusCode(),
            )
        );
    }
}
