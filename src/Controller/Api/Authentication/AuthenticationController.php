<?php

declare(strict_types=1);

namespace App\Controller\Api\Authentication;

use App\Attribute\RateLimit;
use App\Controller\Api\AbstractController;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\SignInDTO;
use App\Enum\InvestmentGoal;
use App\Enum\NotificationType;
use App\Enum\PreferredIndustry;
use App\Enum\RateLimiterTypes;
use App\Enum\RiskTolerance;
use App\Enum\SerializerFormat;
use App\Exception\Security\InvalidCredentialsException;
use App\Notification\NotificationDispatcher;
use App\Security\Authentication;
use App\Security\Otp\OtpGenerator;
use App\Security\Session\Session;
use App\Security\TokenManager;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/api/v1/authentication', name: 'api_authentication_')]
final class AuthenticationController extends AbstractController
{
    public function __construct(
        private readonly Authentication         $authentication,
        private readonly SerializerInterface    $serializer,
        private readonly Session                $session,
        private readonly NotificationDispatcher $notificationDispatcher,
        private readonly TokenManager           $tokenManager,
        private readonly ValidatorInterface     $validator,
        private readonly OtpGenerator           $otpGenerator,
    )
    { }

    /**
     * Authenticate if the User is existing in the system
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[RateLimit(RateLimiterTypes::LOGIN_IP)]
    #[RateLimit(RateLimiterTypes::LOGIN, identifierField: 'email')]
    #[Route(path: '/login', name: 'login', methods: 'POST')]
    public function authenticateUser(Request $request): JsonResponse
    {
        try {
            $parameters = $this->serializer->deserialize(
                $request->getContent(),
                SignInDTO::class,
                SerializerFormat::JSON->value
            );
        } catch (ExceptionInterface) {
            return $this->json(
                ['status' => false, 'message' => 'Invalid JSON payload'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $violations = $this->validator->validate($parameters);
        $constraintViolation = $this->constraintViolationJsonResponse($violations);
        if ($constraintViolation !== null) {
            return $constraintViolation;
        }

        try {
            $user = $this->authentication->authenticateUser($parameters);

            // Prevent session fixation
            $this->session->regenerate();
            $this->session->setAuthentication([
                'id'       => $user->getId(),
                'fullName' => $user->getFullName(),
                'email'    => $user->getUserIdentifier(),
            ]);

            // Commented out due to low service limit
//            $this->notificationDispatcher->notify(NotificationType::LOGIN_OTP, $user);

            $token = $this->tokenManager->generateAccessToken($user);

            $otpCode = $this->otpGenerator->generate();

            return $this->json(['status' => true, 'token' => $token, 'code' => $otpCode]);
        } catch (InvalidCredentialsException $credentialsException) {
            return $this->json(
                ['status' => false, 'message' => $credentialsException->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        }
    }

    /**
     * Register new User to the system
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[RateLimit(RateLimiterTypes::REGISTER)]
    #[Route(path: '/register', name: 'register', methods: 'POST')]
    public function registerUser(Request $request): JsonResponse
    {
        try {
            $parameters = $this->serializer->deserialize(
                $request->getContent(),
                RegisterDTO::class,
                SerializerFormat::JSON->value
            );
        } catch (ExceptionInterface) {
            return $this->json(
                ['status' => false, 'message' => 'Invalid JSON payload'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->normalizeEnumFields($parameters, [
            'investmentGoals'   => InvestmentGoal::class,
            'riskTolerance'     => RiskTolerance::class,
            'preferredIndustry' => PreferredIndustry::class,
        ]);

        $violations = $this->validator->validate($parameters);
        $constraintViolation = $this->constraintViolationJsonResponse($violations);
        if ($constraintViolation !== null) {
            return $constraintViolation;
        }

        try {
            $this->authentication->persistUserRegistration($parameters);

            return $this->json(['status' => true], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return $this->json(
                ['status' => false, 'message' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
