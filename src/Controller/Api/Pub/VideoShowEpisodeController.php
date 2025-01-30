<?php

declare(strict_types=1);

namespace App\Controller\Api\Pub;

use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use App\Controller\AbstractApiPubController;
use App\Controller\Api\Pub\Traits\VideoShowTrait;
use App\Exception\PubNotFoundHttpException;
use App\Model\Domain\VideoShowEpisode\VideoShowEpisodePubDecorator;
use App\Model\Request\ApiPubParams;
use App\Model\Request\CacheSettings;
use App\Repository\Decorator\VideoShowEpisodeRepositoryDecorator;
use App\Repository\VideoShowEpisodeRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[Route(path: '', name: 'pub_video_show_episode_v1_')]
#[OA\Tag('VideoShowEpisode')]
final class VideoShowEpisodeController extends AbstractApiPubController
{
    use VideoShowTrait;

    public function __construct(
        private readonly VideoShowEpisodeRepositoryDecorator $repositoryDecorator,
        private readonly VideoShowEpisodeRepository $videoShowEpisodeRepository,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(
        path: '/{slug}/video-show-episodes/{videoShowEpisodeId}',
        name: 'get_one',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
            'videoShowEpisodeId' => Requirement::UUID,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OA\QueryParameter('videoShowEpisodeId', 'videoShowEpisodeId', description: 'Uuid of the VideoShowEpisode', schema: new OA\Schema(type: 'string'))]
    #[OAResponse(VideoShowEpisodePubDecorator::class)]
    public function getOne(string $slug, string $videoShowEpisodeId): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);
        $videoShowEpisode = $this->videoShowEpisodeRepository->find($videoShowEpisodeId);
        if (false === $videoShowEpisode instanceof VideoShowEpisode) {
            throw new PubNotFoundHttpException('VideoShowEpisode not found');
        }

        if ($videoShowEpisode->getVideoShow()->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new PubNotFoundHttpException('VideoShowEpisode not found');
        }

        return $this->okCachedResponse(
            data: VideoShowEpisodePubDecorator::getInstance($videoShowEpisode),
            cacheSettings: new CacheSettings()
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(
        path: '/{slug}/video-shows/{videoShowId}/video-show-episodes',
        name: 'getList',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
            'podcast' => Requirement::UUID,
            'videoShow' => Requirement::UUID,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\QueryParameter('videoShowId', 'videoShowId', description: 'Uuid of the VideoShow', schema: new OA\Schema(type: 'string'))]
    #[OA\QueryParameter('page', 'page', schema: new OA\Schema(type: 'integer', default: 1, maximum: ApiPubParams::MAX_PAGE, minimum: 1))]
    #[OA\QueryParameter('limit', 'limit', schema: new OA\Schema(type: 'integer', default: ApiPubParams::LIMIT_DEFAULT, minimum: 1, enum: ApiPubParams::ALLOWED_LIMITS))]
    #[OA\QueryParameter('excludeIds[]', 'excludeIds[]', schema: new OA\Schema(type: 'string', maxItems: ApiPubParams::MAX_EXCLUDED_IDS))]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OAResponse([VideoShowEpisodePubDecorator::class])]
    public function getList(string $slug, string $videoShowId, ApiPubParams $apiParams): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);

        return $this->okCachedResponse(
            data: $this->repositoryDecorator->getList(
                publicExport: $publicExport,
                videoShow: $this->getVideoShow(
                    publicExport: $publicExport,
                    videoShowId: $videoShowId,
                ),
                apiParams: $apiParams,
            ),
            cacheSettings: new CacheSettings()
        );
    }
}
