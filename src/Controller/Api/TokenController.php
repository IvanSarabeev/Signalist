<?php

namespace App\Controller\Api;

use App\Entity\RefreshTokens;
use App\Repository\UserRepository;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/token', name: 'api_token_')]
final class TokenController extends AbstractController
{
    public function __construct(
        private readonly TokenGenerator $tokenGenerator,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
    )
    { }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RandomException
     */
    #[Route(path: '/refresh', name: 'refresh', methods: 'POST')]
    public function refresh(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $refreshToken = $data['refresh_token'] ?? null;

        if (!$refreshToken) {
            return $this->json(['message' => 'Invalid token provided.'], Response::HTTP_BAD_REQUEST);
        }

        $tokenEntity = $this->tokenGenerator->validateToken($refreshToken);

        $user = $this->userRepository->findOneBy(['userId' => $tokenEntity->getUserId()]);
        if (!$user) {
            return $this->json(['message' => 'User not found.'], Response::HTTP_NOT_FOUND);
        }

        $newAccessToken = $this->tokenGenerator->generateAccessToken($user->getId());
        $newRefreshToken = $this->tokenGenerator->refreshToken($user->getId());

        $this->tokenGenerator->revokeRefreshToken($tokenEntity);

        return $this->json(['access_token' => $newAccessToken, 'refresh_token' => $newRefreshToken]);
    }

    #[Route(path: '/revoke-all', name: 'revoke_all', methods: 'POST')]
    public function revokeAll(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json([['message' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED]);
        }

        $tokens = $this->entityManager->getRepository(RefreshTokens::class)
            ->findBy(['email' => $user->getUserIdentifier(), 'revoked' => false]);

        foreach ($tokens as $token) {
            $token->setRevoked(true);
        }

        $this->entityManager->flush();

        return $this->json(['message' => 'Token revoked.']);
    }
}
