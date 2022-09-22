<?php

declare(strict_types=1);

namespace App\Controller\Api;

use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'auth_')]
final class LoginController extends AbstractApiController
{
    #[Route('login', name: 'login', methods: [Request::METHOD_POST])]
    public function login(): JsonResponse
    {
        return $this->noContentResponse();
    }
}
