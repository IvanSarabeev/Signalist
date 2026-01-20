<?php

namespace App\Controller\Api;

use App\Controller\Traits\ValidatesRequestTrait;
use App\DTO\Auth\RegisterDTO;
use App\DTO\Auth\SignInDTO;
use App\Enum\SerializerFormat;
use App\Exception\Security\EmailExistsException;
use App\Exception\Security\InvalidCredentialsException;
use App\Exception\Security\UserAlreadyExistsException;
use App\Exception\Security\UserRegistrationFailedException;
use App\Service\Authentication;
use App\Service\Session;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/authentication', name: 'api_authentication', format: 'json')]
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
    #[Route(path: '/login', name: 'login', methods: 'POST')]
    public function authenticateUser(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
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

        $errors = $this->validateConstraints($dto);
        if (!empty($errors)) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = $this->authentication->authenticateUser($dto);

            $this->session->setAuthenticationSettings([
                'id' => $user->getId(),
                'fullName' => $user->getFullName(),
                'email' => $user->getUserIdentifier(),
                'country' => $user->getCountry(),
                'roles' => $user->getRoles(),
            ]);

            return $this->json(['status' => true]);
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
    #[Route(path: '/register', name: 'register', methods: 'POST')]
    public function registerUser(Request $request): JsonResponse
    {
        try {
            $dtoParameters = $this->serializer->deserialize(
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

        $constraintValidation = $this->validateConstraints($dtoParameters);
        if (!empty($constraintValidation)) {
            return $this->json($constraintValidation, Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->authentication->persistUserRegistration($dtoParameters);

            $this->session->setAuthenticationSettings(...$dtoParameters);

            return $this->json(['status' => true], Response::HTTP_CREATED);
        } catch (EmailExistsException|UserAlreadyExistsException $existsException) {
            return $this->json(
                ['status' => false, 'message' => $existsException->getMessage()],
                Response::HTTP_CONFLICT
            );
        }catch (UserRegistrationFailedException|Exception $exception) {
            return $this->json(
                ['status' => false, 'message' => $exception->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route(path: '/logout', name: 'logout', methods: 'POST')]
    public function signOut(Request $request): Response
    {
        if ($request->getSession()->has('login_settings')) {
            $request->getSession()->remove('login_settings');
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        throw new AccessDeniedHttpException('Access denied!');
    }
}
