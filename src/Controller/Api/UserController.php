<?php

namespace App\Controller\Api;

use App\Mapper\User\UserMapper;
use App\Response\ApiResponse;
use App\Security\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/user', name: 'api_v1_user_')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserMapper  $userMapper
    ) { }

    #[Route(path: '', name: 'current', methods: 'GET')]
    public function index(): JsonResponse
    {
        $user = $this->userService->getAuthenticatedUser();

        if (!$user) {
            return ApiResponse::error('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $dtoResponse = $this->userMapper->toDTO($user);

        return ApiResponse::success($dtoResponse);
    }
}
