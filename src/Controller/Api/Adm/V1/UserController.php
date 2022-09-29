<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Request\ParamConverter\ApiFilterParamConverter;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use App\Entity\User;
use App\Repository\UserRepository;
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
        $this->denyAccessUnlessGranted(CorePermissions::CORE_USER_VIEW, $user);

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
        return $this->okResponse(
            $this->userRepo->findByApiParams($apiParams),
        );
    }
}
