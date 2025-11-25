<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Empty response at "/" with 200 status code is required by Kubernetes first initial health check.
 */
final class HomeController extends AbstractController
{
    #[Route('/', 'home', methods: [Request::METHOD_GET])]
    public function home(): Response
    {
        return new Response();
    }
}
