<?php

namespace App\Presentation\Http\ArgumentResolver\Auth;

use App\Presentation\Http\Exception\RequestValidationException;
use App\Presentation\Http\Request\Auth\ValidateOtpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ValidateOtpRequestResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    { }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== ValidateOtpRequest::class) {
            return [];
        }

        $data = $this->extractData($request);

        $otpRequest = new ValidateOtpRequest($data['otp']);

        $violations = $this->validator->validate($otpRequest);

        if (count($violations) > 0) {
            throw new RequestValidationException($violations);
        }

        yield $otpRequest;
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
        return str_contains($request->headers->get('Content-Type'), 'application/json');
    }
}
