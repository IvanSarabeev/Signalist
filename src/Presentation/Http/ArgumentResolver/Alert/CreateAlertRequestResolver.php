<?php

declare(strict_types=1);

namespace App\Presentation\Http\ArgumentResolver\Alert;

use App\Presentation\Http\Request\Alert\CreateAlertRequest;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class CreateAlertRequestResolver implements ValueResolverInterface
{
    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     * @return iterable
     * @throws Exception
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== CreateAlertRequest::class) {
            return [];
        }

        $data = $this->extractData($request);

        yield new CreateAlertRequest(
            symbol:           (string) ($data['symbol'] ?? ''),
            alertName:        (string) ($data['alertName'] ?? ''),
            alertType:        (string) ($data['alertType'] ?? ''),
            conditionQuality: (string) ($data['conditionQuality'] ?? ''),
            frequency:        (string) ($data['frequency'] ?? ''),
            thresholdValue:   (string) ($data['thresholdValue'] ?? ''),
        );
    }

    private function extractData(Request $request): array
    {
        if ($this->isJson($request)) {
            return json_decode($request->getContent(), associative: true) ?? [];
        }

        return $request->request->all();
    }

    private function isJson(Request $request): bool
    {
        return str_contains($request->headers->get('Content-Type', ''), 'application/json');
    }
}
