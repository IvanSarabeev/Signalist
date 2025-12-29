<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends BaseController
{
    #[Route('/', 'app_home')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
