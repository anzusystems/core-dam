<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseDeleted;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\CommonBundle\Request\ParamConverter\ApiFilterParamConverter;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\SerializerBundle\Request\ParamConverter\SerializerParamConverter;
use App\App;
use App\Domain\PermissionGroup\PermissionGroupFacade;
use App\Entity\PermissionGroup;
use App\Model\Domain\PermissionGroup\PermissionGroupCollectionDto;
use App\Repository\PermissionGroupRepository;
use App\Security\Permission\DamPermissions;
use App\Security\Permission\UserPermissionResolver;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('', 'adm_permissionGroup_v1_')]
#[OA\Tag('PermissionGroup')]
final class PermissionGroupController extends AbstractApiController
{
    public function __construct(
        private readonly PermissionGroupFacade $permissionGroupFacade,
        private readonly PermissionGroupRepository $permissionGroupRepo
    ) {
    }

    /**
     * Get list of all items.
     */
    #[Route('/permission-group/all-details', name: 'get_all', methods: [Request::METHOD_GET])]
    #[OA\Response(response: Response::HTTP_OK, description: 'List of all permissions with details.')]
    public function getAll(): JsonResponse
    {
        return new JsonResponse(DamPermissions::allDetail());
    }

    /**
     * Get one item.
     */
    #[Route('/permission-group/{permissionGroup}', 'get_one', ['permissionGroup' => '\d+'], methods: [Request::METHOD_GET])]
    #[OAParameterPath('permissionGroup'), OAResponse(PermissionGroup::class)]
    public function getOne(PermissionGroup $permissionGroup): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_PERMISSION_GROUP_VIEW, $permissionGroup);

        return $this->okResponse($permissionGroup);
    }

    /**
     * Get list of items.
     *
     * @throws ORMException
     */
    #[Route('/permission-group', 'get_list', methods: [Request::METHOD_GET])]
    #[ParamConverter('apiParams', converter: ApiFilterParamConverter::class)]
    #[OAResponse([PermissionGroup::class])]
    public function getList(ApiParams $apiParams): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_PERMISSION_GROUP_VIEW);

        return $this->okResponse(
            $this->permissionGroupRepo->findByApiParams($apiParams),
        );
    }

    #[Route('/permission-group/preview', 'preview', methods: [Request::METHOD_POST])]
    #[ParamConverter('permissionGroupColDto', converter: SerializerParamConverter::class)]
    #[OARequest(PermissionGroupCollectionDto::class)]
    #[OA\Response(response: Response::HTTP_OK, description: 'List of resolved permissions.')]
    public function preview(PermissionGroupCollectionDto $permissionGroupColDto): JsonResponse
    {
        return new JsonResponse(UserPermissionResolver::resolveForGroups($permissionGroupColDto->getPermissionGroups()));
    }

    /**
     * Create item.
     *
     * @throws ValidationException|AppReadOnlyModeException
     */
    #[Route('/permission-group', 'create', methods: [Request::METHOD_POST])]
    #[ParamConverter('permissionGroup', converter: SerializerParamConverter::class)]
    #[OARequest(PermissionGroup::class), OAResponseCreated(PermissionGroup::class), OAResponseValidation]
    public function create(PermissionGroup $permissionGroup): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_PERMISSION_GROUP_CREATE);

        return $this->createdResponse(
            $this->permissionGroupFacade->create($permissionGroup)
        );
    }

    /**
     * Update item.
     *
     * @throws AppReadOnlyModeException
     * @throws ValidationException
     */
    #[Route('/permission-group/{permissionGroup}', 'update', ['permissionGroup' => '\d+'], methods: [Request::METHOD_PUT])]
    #[ParamConverter('newPermissionGroup', converter: SerializerParamConverter::class)]
    #[OAParameterPath('permissionGroup'), OARequest(PermissionGroup::class), OAResponse(PermissionGroup::class), OAResponseValidation]
    public function update(PermissionGroup $permissionGroup, PermissionGroup $newPermissionGroup): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_PERMISSION_GROUP_UPDATE, $permissionGroup);

        return $this->okResponse(
            $this->permissionGroupFacade->update($permissionGroup, $newPermissionGroup)
        );
    }

    /**
     * Delete item.
     *
     * @throws AppReadOnlyModeException
     */
    #[Route('/permission-group/{permissionGroup}', 'delete', ['permissionGroup' => '\d+'], methods: [Request::METHOD_DELETE])]
    #[OAParameterPath('permissionGroup'), OAResponseDeleted]
    public function delete(PermissionGroup $permissionGroup): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(DamPermissions::DAM_PERMISSION_GROUP_DELETE, $permissionGroup);

        $this->permissionGroupFacade->delete($permissionGroup);

        return $this->noContentResponse();
    }
}
