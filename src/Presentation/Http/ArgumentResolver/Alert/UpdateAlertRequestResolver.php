<?php

declare(strict_types=1);

namespace App\Presentation\Http\ArgumentResolver\Alert;

use App\Presentation\Http\Request\Alert\UpdateAlertRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[AsTargetedValueResolver('update_alert_request')]
final class UpdateAlertRequestResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UpdateAlertRequest::class) {
            return [];
        }

        $data = $this->extractData($request);

        yield new UpdateAlertRequest(
            alertName:        $data['alertName'] ?? null,
            alertType:        $data['alertType'] ?? null,
            conditionQuality: $data['conditionQuality'] ?? null,
            frequency:        $data['frequency'] ?? null,
            thresholdValue:   $data['thresholdValue'] ?? null,
            isActive:         isset($data['isActive']) ? (bool) ($data['isActive']) : null,
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
