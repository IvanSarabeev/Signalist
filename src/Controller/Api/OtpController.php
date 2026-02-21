<?php

namespace App\Controller\Api;

use App\Attribute\RateLimit;
use App\Controller\Traits\ValidatesRequestTrait;
use App\DTO\Otp\VerifyOtpRequest;
use App\Enum\RateLimiterTypes;
use App\Enum\SerializerFormat;
use App\Exception\Security\InvalidOtpCredentialsException;
use App\Security\Otp\OtpGenerator;
use App\Security\Otp\OtpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/v1/otp', name: 'api_otp_')]
final class OtpController extends AbstractController
{
    use ValidatesRequestTrait;

    public function __construct(
        private readonly OtpGenerator $otpGenerator,
        private readonly OtpService $otpService,
        private readonly SerializerInterface $serializer,
    ) { }

    #[RateLimit(RateLimiterTypes::OTP)]
    #[Route('/verify', name: 'verify', methods: 'POST')]
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $parameters = $this->serializer->deserialize(
                $request->getContent(),
                VerifyOtpRequest::class,
                SerializerFormat::JSON->value
            );
        } catch (ExceptionInterface) {
            return $this->json(
                ['status' => false, 'message' => 'Invalid JSON payload'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $constraintValidation = $this->validateConstraints($parameters);
        if (!empty($constraintValidation)) {
            return $this->json($constraintValidation, Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->otpService->validateVerificationCode();

//        TODO: use/add JWT authorization token.
            return $this->json(['status' => true, 'token' => ''], Response::HTTP_ACCEPTED);
        } catch (InvalidOtpCredentialsException $otpCredentialsException) {
            return $this->json(
                ['status' => false, 'message' => $otpCredentialsException->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        } catch (UserNotFoundException $notFoundException) {
            return $this->json(
                ['status' => false, 'message' => $notFoundException->getMessage()],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
