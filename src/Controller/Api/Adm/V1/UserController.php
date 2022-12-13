<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\CommonBundle\Request\ParamConverter\ApiFilterParamConverter;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use AnzuSystems\SerializerBundle\Request\ParamConverter\SerializerParamConverter;
use App\App;
use App\Domain\User\UserFacade;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateUserDto;
use App\Repository\UserRepository;
use App\Security\Permission\DamPermissions;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user', 'adm_user_v1_')]
#[OA\Tag('User')]
final class UserController extends AbstractApiController
{
    public function __construct(
        private readonly UserFacade $userFacade,
        private readonly UserRepository $userRepo,
    ) {
    }
    
    #[Route('/current', 'get_current', methods: [Request::METHOD_GET])]
    #[OAResponse(User::class)]
    public function getCurrent(): JsonResponse
    {
        return $this->okResponse($this->getUser());
    }

    /**
     * Get one item.
     */
    #[Route('/{user}', 'get_one', ['user' => '\d+'], methods: [Request::METHOD_GET])]
    #[OAParameterPath('user'), OAResponse(User::class)]
    public function getOne(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_VIEW, $user);

        return $this->okResponse($user);
    }

    /**
     * Get list of items.
     *
     * @throws ORMException
     */
    #[Route('', 'get_list', methods: [Request::METHOD_GET])]
    #[ParamConverter('apiParams', converter: ApiFilterParamConverter::class)]
    #[OAResponse([User::class])]
    public function getList(ApiParams $apiParams): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_VIEW);

        return $this->okResponse(
            $this->userRepo->findByApiParamsWithInfiniteListing($apiParams),
        );
    }

    /**
     * Create item.
     *
     * @throws ValidationException
     * @throws AppReadOnlyModeException
     */
    #[Route('', 'create', methods: [Request::METHOD_POST])]
    #[ParamConverter('createUserDto', converter: SerializerParamConverter::class)]
    #[OARequest(CreateUserDto::class), OAResponseCreated(User::class), OAResponseValidation]
    public function create(CreateUserDto $createUserDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_CREATE);

        return $this->createdResponse(
            $this->userFacade->createFromDto($createUserDto)
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
    #[ParamConverter('updateUserDto', converter: SerializerParamConverter::class)]
    #[OAParameterPath('user'), OARequest(UpdateUserDto::class), OAResponse(User::class), OAResponseValidation]
    public function update(User $user, UpdateUserDto $updateUserDto): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_USER_UPDATE, $user);

        return $this->okResponse(
            $this->userFacade->updateFromDto($user, $updateUserDto)
        );
    }
}
