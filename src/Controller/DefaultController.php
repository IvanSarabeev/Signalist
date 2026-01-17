<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class DefaultController extends AbstractController
{
    #[Route('/', 'app_home')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
