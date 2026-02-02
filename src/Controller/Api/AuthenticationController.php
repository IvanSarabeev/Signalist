<?php

namespace App\Controller\Api;

use App\Attribute\RateLimit;
use App\Controller\Traits\ValidatesRequestTrait;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\SignInDTO;
use App\Enum\InvestmentGoal;
use App\Enum\PreferredIndustry;
use App\Enum\RateLimiterTypes;
use App\Enum\RiskTolerance;
use App\Enum\SerializerFormat;
use App\Exception\Security\EmailExistsException;
use App\Exception\Security\InvalidCredentialsException;
use App\Exception\Security\UserAlreadyExistsException;
use App\Exception\Security\UserRegistrationFailedException;
use App\Service\Authentication;
use App\Service\Session\Session;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/authentication', name: 'api_authentication_')]
final class AuthenticationController extends AbstractController
{
    use ValidatesRequestTrait;

    public function __construct(
        private readonly Authentication      $authentication,
        private readonly SerializerInterface $serializer,
        private readonly Session             $session,
    ) {
    }

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
                Response::HTTP_BAD_REQUEST
            );
        }

        $constraintValidation = $this->validateConstraints($parameters);
        if (!empty($constraintValidation)) {
            return $this->json($constraintValidation, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $this->authentication->authenticateUser($parameters);

            // Prevent session fixation
            $this->session->regenerate();
            $this->session->setAuthentication([
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getUserIdentifier(),
            ]);

            return $this->json(['status' => true], Response::HTTP_ACCEPTED);
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
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->normalizeEnumFields($parameters, [
            'investmentGoals'    => InvestmentGoal::class,
            'riskTolerance'     => RiskTolerance::class,
            'preferredIndustry' => PreferredIndustry::class,
        ]);

        $constraintValidation = $this->validateConstraints($parameters);
        if (!empty($constraintValidation)) {
            return $this->json($constraintValidation, Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->authentication->persistUserRegistration($parameters);

            return $this->json(['status' => true], Response::HTTP_CREATED);
        } catch (EmailExistsException|UserAlreadyExistsException $existsException) {
            return $this->json(
                ['status' => false, 'message' => $existsException->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (UserRegistrationFailedException|Exception $exception) {
            return $this->json(
                ['status' => false, 'message' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route(path: '/logout', name: 'logout', methods: 'POST')]
    public function signOut(): Response
    {
       if ($this->session->hasAuthentication()) {
           $this->session->clearAuthentication();
           $this->session->regenerate();
       }

       return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
