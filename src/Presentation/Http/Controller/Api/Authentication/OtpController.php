<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Api\Authentication;

use App\Enum\Security\RateLimiter;
use App\Presentation\Http\Attribute\RateLimit;
use App\Presentation\Http\Controller\Api\AbstractController;
use App\Presentation\Http\Request\Auth\ValidateOtpRequest;
use App\Security\Otp\OtpService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/otp', name: 'api_otp_')]
#[OA\Tag(name: 'OTP')]
final class OtpController extends AbstractController
{
    public function __construct(private readonly OtpService $otpService)
    { }

//    #[RateLimit(RateLimiter::OTP->value, identifierField: 'otp')]
    #[Route('/verify', name: 'verify', methods: ['POST'])]
    public function verifyOtp(ValidateOtpRequest $otpRequest): JsonResponse
    {
        return $this->json(['status' => true], Response::HTTP_ACCEPTED);
        $this->otpService->validateVerificationCode($otpRequest);

        return $this->json(['status' => true], Response::HTTP_ACCEPTED);
    }

    #[Route(path: '/resend', name: 'resend', methods: ['POST'])]
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
