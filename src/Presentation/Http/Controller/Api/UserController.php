<?php

namespace App\Presentation\Http\Controller\Api;

use App\Entity\User;
use App\Presentation\Http\Response\ApiResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/api/v1/user', name: 'api_v1_user_')]
#[OA\Tag(name: 'User')]
final class UserController
{
    #[Route(path: '', name: 'current', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return ApiResponse::error('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        return ApiResponse::success($user->toArray());
    }
}
