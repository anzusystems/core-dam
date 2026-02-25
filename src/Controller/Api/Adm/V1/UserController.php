<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseInfiniteList;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\App;
use App\Domain\User\DeprecatedUserFacade;
use App\Entity\User;
use App\Model\Domain\User\DeprecatedCurrentUserDto;
use App\Model\Domain\User\DeprecatedUpdateUserDto;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Repository\UserRepository;
use App\Security\Permission\DamPermissions;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @method User getUser()
 */
#[Route('/user', 'adm_user_v1_')]
#[OA\Tag('User')]
final class UserController extends AbstractApiController
{
    public function __construct(
        private readonly DeprecatedUserFacade $userFacade,
        private readonly UserRepository $userRepo,
    ) {
    }

    /**
     * Get one item.
     */
    #[Route('/current', 'get_current', methods: [Request::METHOD_GET])]
    #[OAResponse(DeprecatedCurrentUserDto::class)]
    public function getCurrent(): JsonResponse
    {
        return $this->okResponse(
            DeprecatedCurrentUserDto::getInstance($this->getUser())
        );
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    #[Route('/current', 'update_current', methods: [Request::METHOD_PATCH])]
    #[OARequest(UpdateCurrentUserDto::class), OAResponse(User::class), OAResponseValidation]
    public function updateCurrent(#[SerializeParam] UpdateCurrentUserDto $updateDto): JsonResponse
    {
        $user = $this->userFacade->updateFromCurrentUserDto($this->getUser(), $updateDto);

        return $this->okResponse(DeprecatedCurrentUserDto::getInstance($user));
    }

    /**
     * Get one item.
     */
    #[Route('/{user}', 'get_one', ['user' => '\d+'], methods: [Request::METHOD_GET])]
    #[OAParameterPath('user'), OAResponse(User::class)]
    public function getOne(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_READ, $user);

        return $this->okResponse($user);
    }

    /**
     * Get list of items.
     *
     * @throws ORMException
     */
    #[Route('', 'get_list', methods: [Request::METHOD_GET])]
    #[OAResponseInfiniteList(User::class)]
    public function getList(ApiParams $apiParams): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_READ);

        return $this->okResponse(
            $this->userRepo->findByApiParamsWithInfiniteListing($apiParams),
        );
    }

    /**
     * Update item.
     *
     * @throws AppReadOnlyModeException
     * @throws ValidationException
     * @throws SerializerException
     */
    #[Route('/{user}', 'update', ['user' => '\d+'], methods: [Request::METHOD_PUT])]
    #[OAParameterPath('user'), OARequest(DeprecatedUpdateUserDto::class), OAResponse(User::class), OAResponseValidation]
    public function update(User $user, #[SerializeParam] DeprecatedUpdateUserDto $updateUserDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_UPDATE, $user);

        return $this->okResponse(
            $this->userFacade->updateFromDto($user, $updateUserDto)
        );
    }
}
