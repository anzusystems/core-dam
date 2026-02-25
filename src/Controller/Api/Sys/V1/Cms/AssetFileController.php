<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1\Cms;

use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Repository\AssetFileRepository;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\Model\Domain\Asset\AssetFileCmsSysDecorator;
use App\Model\Dto\UuidListDto;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag('AssetFile')]
#[Route('/cms/asset-file', 'sys_asset_file_cms_')]
final class AssetFileController extends AbstractApiController
{
    public function __construct(
        private readonly AssetFileRepository $assetFileRepository,
    ) {
    }

    #[Route('', 'get_list', methods: [Request::METHOD_GET])]
    public function getList(#[SerializeParam] UuidListDto $ids): JsonResponse
    {
        $assets = $this->assetFileRepository->findByIds($ids->getIds());
        if ($assets->isEmpty()) {
            throw new NotFoundHttpException('Assets not found');
        }

        return $this->okResponse(
            new ApiResponseList()
                ->setData(
                    $assets->map(
                        fn (AssetFile $assetFile): AssetFileCmsSysDecorator => AssetFileCmsSysDecorator::getInstance($assetFile),
                    )->toArray()
                )
                ->setTotalCount(count($assets))
        );
    }
}
