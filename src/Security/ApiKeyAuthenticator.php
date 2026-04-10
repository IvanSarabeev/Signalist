<?php

namespace App\Security;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly string $jwtSecret)
    { }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        $path = $request->getPathInfo();

        // Allow ONLY authentication routes to bypass auth
        if (str_starts_with($path, '/api/v1/authentication')) {
            return false;
        }

        // Everything else under /api/v1 MUST be authenticated
        return str_starts_with($path, '/api/v1');
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get('Authorization');

        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Missing Authorization header.');
        }

        $extractToken = str_replace('Bearer ', '', $token);

        try {
            $decoded = JWT::decode($extractToken, new Key($this->jwtSecret, 'HS256'));

            if (!isset($decoded->sub)) {
                throw new CustomUserMessageAuthenticationException('Token missing "sub" claim.');
            }
        } catch (Exception $exception) {
            throw new CustomUserMessageAuthenticationException('Invalid or expired token: ' . $exception->getMessage());
        }

        return new SelfValidatingPassport(
            new UserBadge((string) $decoded->sub)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'status' => false,
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
