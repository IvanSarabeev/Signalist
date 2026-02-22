<?php

namespace App\EventListener;

use App\Exception\DomainException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener(event: 'kernel.exception')]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$this->isApiRequest($event)) {
            // Continue with the exception forward
            return;
        }

        if ($exception instanceof DomainException) {
            $event->setResponse(
                new JsonResponse(
                    ['status' => false, 'message' => $exception->getErrorMessage()],
                    $exception->getStatusCode()
                )
            );
        }
    }


    private function isApiRequest(ExceptionEvent $event): bool
    {
        return str_starts_with(
            $event->getRequest()->getPathInfo(),
            '/api/'
        );
    }
}
