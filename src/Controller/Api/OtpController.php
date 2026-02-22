<?php

namespace App\Controller\Api;

use App\Attribute\RateLimit;
use App\Controller\Traits\ValidatesRequestTrait;
use App\DTO\Otp\VerifyOtpRequest;
use App\Enum\RateLimiterTypes;
use App\Enum\SerializerFormat;
use App\Exception\Security\ExpiredOtpException;
use App\Security\Otp\OtpService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api/v1/otp', name: 'api_otp_')]
final class OtpController extends AbstractController
{
    use ValidatesRequestTrait;

    public function __construct(
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

        $this->otpService->validateVerificationCode($parameters);

        return $this->json(['status' => true]);
    }

    #[Route(path: '/resend', name: 'resend', methods: 'POST')]
    public function resend(): JsonResponse
    {
        /*
         * Receive the token... validate the token
         * If the token is empty or expired throw and 400 + status code
         * Get the userId from the RefreshToken Entity
         * If the userId is missing or the User isn't found return an error
         * If the User is existing use the Notification/Messenger Layer and send them once again an Email
         * with the newest OTP Code.
         * If the User tries to resend the token for a total of 3 times remove their token and redirect them to the /sign-in Page.
         */

        return $this->json(['message' => 'Resend OTP']);
    }
}
