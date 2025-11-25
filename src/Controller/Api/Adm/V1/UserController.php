<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseInfiniteList;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Permission\DamPermissions;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', 'adm_user_v1_')]
#[OA\Tag('Users')]
final class UserController extends AbstractApiController
{
    public function __construct(
        private readonly UserRepository $userRepo,
    ) {
    }

    /**
     * @throws ORMException
     */
    #[Route('', 'user', methods: [Request::METHOD_GET])]
    #[OAResponseInfiniteList(UserDto::class)]
    public function getList(ApiParams $apiParams): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_READ);

        return $this->okResponse(
            $this->userRepo->findByApiParamsWithInfiniteListing(
                apiParams: $apiParams,
                mapDataFn: fn (User $user): UserDto => UserDto::createFromUser($user)
            ),
        );
    }
}
