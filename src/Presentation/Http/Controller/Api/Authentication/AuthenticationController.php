<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller\Api\Authentication;

use App\Enum\RateLimiterTypes;
use App\Notification\NotificationDispatcher;
use App\Presentation\Http\Attribute\RateLimit;
use App\Presentation\Http\Controller\Api\AbstractController;
use App\Presentation\Http\Request\Auth\LoginRequest;
use App\Presentation\Http\Request\Auth\RegisterRequest;
use App\Security\Auth\AuthenticationInterface;
use App\Security\Token\TokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/authentication', name: 'api_authentication_')]
#[OA\Tag(name: 'Authentication')]
final class AuthenticationController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationInterface $authentication,
        private readonly NotificationDispatcher  $notificationDispatcher,
        private readonly TokenManagerInterface   $tokenManager,
    )
    { }

    /**
     * Authenticate if the User is existing in the system
     *
     * @param LoginRequest $loginRequest
     * @return JsonResponse
     */
    #[RateLimit(RateLimiterTypes::LOGIN_IP)]
    #[RateLimit(RateLimiterTypes::LOGIN, identifierField: 'email')]
    #[Route(path: '/login', name: 'login', methods: ['POST'])]
    public function authenticateUser(LoginRequest $loginRequest): JsonResponse
    {
        $user = $this->authentication->authenticateUser($loginRequest);

        // Commented out due to low service limit
//            $this->notificationDispatcher->notify(NotificationType::LOGIN_OTP, $user);

        $token = $this->tokenManager->generateAccessToken($user);

        return $this->json(['status' => true, 'token' => $token]);
    }

    /**
     * Register new User to the system
     *
     * @param RegisterRequest $registerRequest
     * @return JsonResponse
     */
    #[RateLimit(RateLimiterTypes::REGISTER)]
    #[Route(path: '/register', name: 'register', methods: ['POST'])]
    public function registerUser(RegisterRequest $registerRequest): JsonResponse
    {
        $this->authentication->persistUserRegistration($registerRequest);

        return $this->json(['status' => true], Response::HTTP_CREATED);
    }
}
