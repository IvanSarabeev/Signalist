<?php

namespace App\Exception;

interface DomainExceptionInterface
{
    /**
     * Get the specified status Code for the Exception::class
     * @return int
     */
    public function getStatusCode(): int;

    /**
     * Get the specific error message based on the Exception::class
     * @return string
     */
    public function getErrorMessage(): string;
}
