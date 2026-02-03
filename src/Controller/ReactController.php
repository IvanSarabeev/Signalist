<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReactController extends AbstractController
{
    #[Route(
        '/{path}',
        name: 'app_react',
        requirements: [
            'path' => '^(?!api|_profiler|_wdt|build).*'
        ],
        defaults: ['path' => null],
        priority: -1000,
    )]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
