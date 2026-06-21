<?php

declare(strict_types=1);

namespace App\Presentation\Http\ArgumentResolver\Auth;

use App\Presentation\Http\Exception\RequestValidationException;
use App\Presentation\Http\Request\Auth\LoginRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class LoginRequestResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    { }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== LoginRequest::class) {
            return [];
        }

        $data = $this->extractData($request);

        $loginRequest = new LoginRequest(
            email:    $data['email'],
            password: $data['password']
        );

        $violations = $this->validator->validate($loginRequest);

        if (count($violations) > 0) {
            throw new RequestValidationException($violations);
        }

        yield $loginRequest;
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
