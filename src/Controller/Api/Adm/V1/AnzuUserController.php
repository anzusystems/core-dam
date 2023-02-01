<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Entity\AnzuUser;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\Contracts\Model\User\UserDto;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\User\UserFacade;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Permission\DamPermissions;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/anzu-user', 'adm_anzu_user_v1_')]
#[OA\Tag('AnzuUser')]
final class AnzuUserController extends AbstractApiController
{
    public function __construct(
        private readonly UserFacade $userFacade,
        private readonly UserRepository $userRepo,
    ) {
    }

    /**
     * Get one item.
     */
    #[Route('/{user}', 'get_one', ['user' => '\d+'], methods: [Request::METHOD_GET])]
    #[OAParameterPath('user'), OAResponse(User::class)]
    public function getOne(AnzuUser $user): JsonResponse
    {
        return $this->okResponse($user);
    }

    /**
     * Get list of items.
     *
     * @throws ORMException
     */
    #[Route('', 'User', methods: [Request::METHOD_GET])]
    #[OAResponse([AnzuUser::class])]
    public function getList(ApiParams $apiParams): JsonResponse
    {
        return $this->okResponse(
            $this->userRepo->findByApiParamsWithInfiniteListing($apiParams),
        );
    }

    /**
     * Update base AnzuUser.
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     * @throws SerializerException
     */
    #[Route('/{user}', 'update', ['user' => '\d+'], methods: [Request::METHOD_PUT])]
    #[OAParameterPath('user'), OARequest(UserDto::class), OAResponse(User::class), OAResponseValidation]
    public function update(User $user, #[SerializeParam] UserDto $userDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_UPDATE);

        return $this->okResponse(
            $this->userFacade->updateAnzuUser($user, $userDto)
        );
    }

    /**
     * Create item.
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route('', 'create', methods: [Request::METHOD_POST])]
    #[OARequest(UserDto::class), OAResponseCreated(User::class), OAResponseValidation]
    public function create(#[SerializeParam] UserDto $userDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_CREATE);

        return $this->createdResponse(
            $this->userFacade->createAnzuUser($userDto)
        );
    }
}
