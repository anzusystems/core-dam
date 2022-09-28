<?php

declare(strict_types=1);

namespace App\Controller\Adm\V1;

use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use App\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', 'adm_user_v1_')]
#[OA\Tag('User')]
final class UserController extends AbstractApiController
{
    #[Route('/current', 'get_current', methods: [Request::METHOD_GET])]
    #[OAResponse(User::class)]
    public function getCurrent(): JsonResponse
    {
        return $this->okResponse($this->getUser());
    }
}
