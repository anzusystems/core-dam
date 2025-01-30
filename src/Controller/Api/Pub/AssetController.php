<?php

declare(strict_types=1);

namespace App\Controller\Api\Pub;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use App\Controller\AbstractApiPubController;
use App\Domain\Asset\AssetPubFacade;
use App\Exception\PubNotFoundHttpException;
use App\Model\Request\CacheSettings;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[Route(path: '', name: 'pub_asset_v1_')]
#[OA\Tag('Asset')]
final class AssetController extends AbstractApiPubController
{
    public function __construct(
        private readonly AssetPubFacade $assetPubFacade,
        private readonly AssetRepository $assetRepository,
    ) {
    }

    /**
     * @throws PubNotFoundHttpException
     */
    #[Route(
        path: '/{slug}/assets/{assetId}',
        name: 'get_asset',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
            'assetId' => Requirement::UUID,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OA\PathParameter('assetId', 'assetId', description: 'AssetId', schema: new OA\Schema(type: 'string'))]
    public function getAsset(string $slug, string $assetId): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);
        $asset = $this->assetRepository->find($assetId);
        if (false === $asset instanceof Asset) {
            throw new PubNotFoundHttpException('Asset not found');
        }
        if ($publicExport->getAssetLicence()->isNot($asset->getLicence())) {
            throw new PubNotFoundHttpException('Asset not found');
        }

        return $this->okCachedResponse(
            data: $this->assetPubFacade->decorateAsset($asset, $publicExport),
            cacheSettings: new CacheSettings()
        );
    }
}
