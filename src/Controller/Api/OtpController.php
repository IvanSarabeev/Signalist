<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Security\OtpGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/otp', name: 'api_otp_')]
final class OtpController extends AbstractController
{
    public function __construct(
        private readonly OtpGenerator $otpGenerator,
    ) { }

    #[Route('/verify', name: 'verify', methods: 'POST')]
    public function verifyOtp(Request $request): JsonResponse
    {
//        $user = $this->entityManager->getRepository(UserRepository::class)
//            ->find($request->request->get('userId'));

//        TODO: use/add JWT authorization token.
        return $this->json(['status' => true, 'token' => 'Alibaba'], Response::HTTP_CREATED);
    }
}
