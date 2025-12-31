<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v1/authentication', name: 'api_authentication')]
final class AuthenticationController extends BaseController
{
    #[Route(path: '/login', name: 'api_login', methods: 'POST')]
    public function authenticateUser(): JsonResponse
    {
        // Will accept the following input
        /**
         * email: '',
         * password: '',
         */

        return $this->json(['status' => true]);
    }

    #[Route(path: '/register', name: 'api_register', methods: 'POST')]
    public function registerUser(): JsonResponse
    {
        // Will accept the following input
        /**
         * fullName: '',
         * email: '',
         * password: '',
         * country: '',
         * investmentGoals: '' Enum -> InvestmentGoal,
         * riskTolerance: '' Enum -> RiskTolerance,
         * preferredIndustry: '' Enum -> PreferredIndustry,
         */

        return $this->json(['status' => true]);
    }

    #[Route(path: '/logout', name: 'api_logout', methods: 'POST')]
    public function signOut(Request $request): Response
    {
        if ($request->getSession()->has('login_settings')) {
            $request->getSession()->remove('login_settings');
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        throw new AccessDeniedHttpException('Access denied!');
    }
}
