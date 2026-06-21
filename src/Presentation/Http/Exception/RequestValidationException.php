<?php

namespace App\Presentation\Http\Exception;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class RequestValidationException extends RuntimeException
{
    private array $errors;

    public function __construct(
        ConstraintViolationListInterface $violation,
        string $message = 'Validation failed.',
        private readonly int $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY,
    )
    {
        $this->errors = $this->buildErrors($violation);

        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function buildErrors(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $errors;
    }
}
