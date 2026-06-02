<?php

namespace App\Presentation\Http\Controller\Api;

use App\Entity\User;
use App\Mapper\User\UserMapper;
use App\Presentation\Http\Response\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/user', name: 'api_v1_user_')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserMapper $userMapper
    ) { }

    #[Route(path: '', name: 'current', methods: 'GET')]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return ApiResponse::error('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        return ApiResponse::success($this->userMapper->toDTO($user));
    }
}
