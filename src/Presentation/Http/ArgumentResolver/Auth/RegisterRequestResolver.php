<?php

declare(strict_types=1);

namespace App\Presentation\Http\ArgumentResolver\Auth;

use App\Presentation\Http\Exception\RequestValidationException;
use App\Presentation\Http\Request\Auth\RegisterRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsTargetedValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsTargetedValueResolver('register_request')]
final readonly class RegisterRequestResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    { }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== RegisterRequest::class) {
            return [];
        }

        $data = $this->extractData($request);

        $registerRequest = new RegisterRequest(
            fullName:          $data['fullName'],
            email:             $data['email'],
            password:          $data['password'],
            country:           $data['country'],
            investmentGoals:   $data['investmentGoals'],
            riskTolerance:     $data['riskTolerance'],
            preferredIndustry: $data['preferredIndustry'],
        );

        $violations = $this->validator->validate($registerRequest);

        if (count($violations) > 0) {
            throw new RequestValidationException($violations);
        }

        yield $registerRequest;
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
