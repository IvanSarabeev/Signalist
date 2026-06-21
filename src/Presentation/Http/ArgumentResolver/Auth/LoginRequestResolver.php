<?php

declare(strict_types=1);

namespace App\Presentation\Http\ArgumentResolver\Auth;

use App\Presentation\Http\Request\Auth\LoginRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class LoginRequestResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== LoginRequest::class) {
            return [];
        }

        $data = $this->extractData($request);

        yield new LoginRequest(
            email:    (string) ($data['email'] ?? ''),
            password: (string) ($data['password'] ?? '')
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
