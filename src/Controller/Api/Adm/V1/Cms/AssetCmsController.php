<?php

declare(strict_types=1);

namespace App\Controller\Api\Adm\V1\Cms;

use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Security\Permission\DamPermissions;
use App\Domain\Asset\AssetCmsFactory;
use App\Model\Domain\Asset\AssetCmsSysDto;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/cms/asset', 'adm_asset_v1_cms_')]
#[OA\Tag('Asset')]
final class AssetCmsController extends AbstractApiController
{
    public function __construct(
        private readonly AssetCmsFactory $assetCmsFactory,
    ) {
    }

    /**
     * Get one item.
     */
    #[Route('/{asset}', 'get_one', ['asset' => Requirement::UUID], methods: [Request::METHOD_GET])]
    #[OAParameterPath('asset'), OAResponse(AssetCmsSysDto::class)]
    public function getOne(Asset $asset): JsonResponse
    {
        $this->denyAccessUnlessGranted(DamPermissions::DAM_ASSET_READ, $asset);

        return $this->okResponse($this->assetCmsFactory->create($asset));
    }
}
