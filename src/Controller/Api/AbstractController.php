<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Exception\Common\InvalidPaginationArgumentException;
use \Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractController extends BaseController
{
    private const DEFAULT_PAGE = 1;
    private const DEFAULT_PAGE_LIMIT = 10;

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
}
