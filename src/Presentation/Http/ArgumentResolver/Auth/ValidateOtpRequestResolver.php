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

        $ptpRequest = new ValidateOtpRequest(
            $request->request->getString('code'),
        );

        $violations = $this->validator->validate($ptpRequest);

        if (count($violations) > 0) {
            throw new RequestValidationException($violations);
        }

        yield $ptpRequest;
    }
}
