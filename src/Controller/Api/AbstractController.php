<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\Common\InvalidPaginationArgumentException;
use InvalidArgumentException;
use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractController extends BaseController
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PAGE_LIMIT = 10;

    /**
     * Create an array Pagination of arguments from the Http Request
     *
     * @param Request $request
     * @param int $defaultPage
     * @param int $defaultPageLimit
     * @return array<string, array{
     *     offset: integer,
     *     fetch_limit: integer,
     * }>
     */
    protected function createPaginationParametersFromRequest(
        Request $request,
        int     $defaultPage = self::DEFAULT_PAGE,
        int     $defaultPageLimit = self::DEFAULT_PAGE_LIMIT
    ): array
    {
        $page = $request->query->getInt('page', $defaultPage);
        $pageLimit = $request->query->getInt('limit', $defaultPageLimit);

        if ($page < 1 || $pageLimit < 1) {
            throw new InvalidPaginationArgumentException();
        }

        return [
            'offset' => ($page - 1) * $pageLimit,
            'fetch_limit' => $pageLimit,
        ];
    }

    /**
     * Validate the given constraint violations and return JsonResponse if there's a mismatch
     *
     * @param ConstraintViolationListInterface $constraintViolationList
     * @param bool $formatMessages
     * @param bool $formatToArray
     * @param int $statusCode - Default to 400 (Client Validation error)
     * @return JsonResponse<string, array{
     *     status: boolean,
     *     invalid_fields: string[],
     *     message: string,
     * }>|null
     */
    protected function constraintViolationJsonResponse(
        ConstraintViolationListInterface $constraintViolationList,
        bool                             $formatMessages = false,
        bool                             $formatToArray = false,
        int                              $statusCode = Response::HTTP_BAD_REQUEST,
    ): ?JsonResponse
    {
        if ($constraintViolationList->count() === 0) {
            return null;
        }

        $invalidFields = [];
        $messages = [];

        foreach ($constraintViolationList as $constraintViolation) {
            if (!$formatToArray) {
                $fields = str_replace([']', '['], '', $constraintViolation->getPropertyPath());
            } else {
                $fields = preg_replace("/^\[(.*?)](.*)$/", "$1$2", $constraintViolation->getPropertyPath());
            }

            $invalidFields = array_merge($invalidFields, explode(', ', $fields ?? ''));
            $message = $constraintViolation->getMessage();

            if (!in_array($message, $messages, true)) {
                $messages[] = $message;
            }
        }

        if ($formatMessages && count($messages) > 1) {
            $messages = array_map(function (string $message) {
                return " - $message";
            }, $messages);
        }

        return $this->json(
            [
                'status' => false,
                'invalid_fields' => $invalidFields,
                'message' => rtrim(implode($formatMessages ? '<br/>' : ', ', $messages))
            ],
            $statusCode
        );
    }

    /**
     * Normalize enum given DTO fields to specific standard
     *
     * @param object $dto
     * @param array $map
     * @return void
     */
    protected function normalizeEnumFields(object $dto, array $map): void
    {
        $reflection = new ReflectionObject($dto);

        foreach ($map as $property => $enumClass) {
            if (!$reflection->hasProperty($property)) {
                continue;
            }

            $prop = $reflection->getProperty($property);
            $prop->setAccessible(true);

            $value = $prop->getValue($dto);

            if (!is_string($value) || $value === '') {
                continue;
            }

            try {
                $prop->setValue($dto, $enumClass::fromLabel($value)->value);
            } catch (InvalidArgumentException) {
                // leave as-is; validator will handle invalid value
            }
        }
    }
}
