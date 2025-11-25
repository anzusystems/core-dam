<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseInfiniteList;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\CommonBundle\Model\User\BaseUserDto;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\App;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\User\UserFacade;
use App\Entity\User;
use App\Model\Domain\User\DamUserDto;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Repository\UserRepository;
use App\Security\Permission\DamPermissions;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method User getUser()
 */
#[Route('/users', 'adm_users_')]
#[OA\Tag('Users')]
final class UserController extends AbstractApiController
{
    public function __construct(
        private readonly UserRepository $userRepo,
        private readonly UserFacade $userFacade,
    ) {
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

        return $this->okResponse(DamUserDto::createFromUser($user));
    }

    #[Route('/current', 'get_current', methods: [Request::METHOD_GET])]
    #[OAResponse(DamUserDto::class)]
    public function getCurrent(): JsonResponse
    {
        return $this->okResponse(
            DamUserDto::createFromUser($this->getUser())
        );
    }

    #[Route('/{user}', 'get_one', ['user' => '\d+'], methods: [Request::METHOD_GET])]
    #[OAParameterPath('user'), OAResponse(DamUserDto::class)]
    public function getOne(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_READ);

        return $this->okResponse(DamUserDto::createFromUser($user));
    }

    /**
     * @throws ORMException
     */
    #[Route('', 'User', methods: [Request::METHOD_GET])]
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

    /**
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route('', 'create', methods: [Request::METHOD_POST])]
    #[OARequest(DamUserDto::class), OAResponseCreated(DamUserDto::class), OAResponseValidation]
    public function create(#[SerializeParam] DamUserDto $userDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_CREATE);

        return $this->createdResponse(
            DamUserDto::createFromUser($this->userFacade->createUser($userDto))
        );
    }

    /**
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route('/{user}', 'update', ['user' => '\d+'], methods: [Request::METHOD_PUT])]
    #[OAParameterPath('user'), OARequest(DamUserDto::class), OAResponse(DamUserDto::class), OAResponseValidation]
    public function update(User $user, #[SerializeParam] DamUserDto $userDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_UPDATE);

        return $this->okResponse(
            DamUserDto::createFromUser($this->userFacade->updateFromDamUserDto($user, $userDto))
        );
    }

    /**
     * Update base AnzuUser.
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route('/{user}', 'update_base', ['user' => '\d+'], methods: [Request::METHOD_PATCH])]
    #[OAParameterPath('user'), OARequest(BaseUserDto::class), OAResponse(DamUserDto::class), OAResponseValidation]
    public function updateBase(User $user, #[SerializeParam] BaseUserDto $baseUserDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_UPDATE);

        return $this->okResponse(
            DamUserDto::createFromUser($this->userFacade->updateFromBaseUserDto($user, $baseUserDto))
        );
    }
}
