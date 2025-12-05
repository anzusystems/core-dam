<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1\Cms;

use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\Domain\Asset\AssetCmsFactory;
use App\Model\Domain\Asset\AssetCmsSysDto;
use App\Model\Dto\UuidListDto;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag('Asset')]
#[Route('/cms/asset', 'sys_asset_cms_')]
final class AssetController extends AbstractApiController
{
    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly AssetCmsFactory $assetCmsFactory,
    ) {
    }

    #[Route('', 'get_list', methods: [Request::METHOD_GET])]
    public function getList(#[SerializeParam] UuidListDto $ids): JsonResponse
    {
        $assets = $this->assetRepository->findByIds($ids->getIds());
        if ($assets->isEmpty()) {
            throw new NotFoundHttpException('Assets not found');
        }

        return $this->okResponse(
            new ApiResponseList()
                ->setData(
                    $assets->map(
                        fn (Asset $asset): AssetCmsSysDto => $this->assetCmsFactory->create($asset),
                    )->toArray()
                )
                ->setTotalCount(count($assets))
        );
    }
}
