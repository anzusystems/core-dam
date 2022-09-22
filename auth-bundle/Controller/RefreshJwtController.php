<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Controller;

use AnzuSystems\CommonBundle\Controller\AbstractAnzuApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'auth_refresh_jwt', methods: [Request::METHOD_POST])]
final class RefreshJwtController extends AbstractAnzuApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}
