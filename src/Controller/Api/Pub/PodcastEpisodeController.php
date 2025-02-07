<?php

declare(strict_types=1);

namespace App\Controller\Api\Pub;

use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use App\Controller\AbstractApiPubController;
use App\Controller\Api\Pub\Traits\PodcastTrait;
use App\Exception\PubNotFoundHttpException;
use App\Model\Domain\PodcastEpisode\PodcastEpisodePubDecorator;
use App\Model\Request\ApiPubParams;
use App\Model\Request\CacheSettings;
use App\Repository\Decorator\PodcastEpisodeRepositoryDecorator;
use App\Repository\PodcastEpisodeRepository;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[Route(path: '', name: 'pub_podcast_episode_v1_')]
#[OA\Tag('PodcastEpisode')]
final class PodcastEpisodeController extends AbstractApiPubController
{
    use PodcastTrait;

    public function __construct(
        private readonly PodcastEpisodeRepositoryDecorator $repositoryDecorator,
        private readonly PodcastEpisodeRepository $podcastEpisodeRepository,
    ) {
    }

    /**
     * @throws PubNotFoundHttpException
     */
    #[Route(
        path: '/{slug}/podcast-episodes/{podcastEpisodeId}',
        name: 'get_one',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
            'podcastEpisodeId' => Requirement::UUID,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OA\QueryParameter('podcastEpisodeId', 'podcastEpisodeId', description: 'Uuid of the PodcastEpisode', schema: new OA\Schema(type: 'string'))]
    #[OAResponse(PodcastEpisodePubDecorator::class)]
    public function getOne(string $slug, string $podcastEpisodeId): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);
        $podcastEpisode = $this->podcastEpisodeRepository->find($podcastEpisodeId);
        if (false === $podcastEpisode instanceof PodcastEpisode) {
            throw new PubNotFoundHttpException('PodcastEpisode not found');
        }

        if ($podcastEpisode->getPodcast()->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new PubNotFoundHttpException('PodcastEpisode not found');
        }

        return $this->okCachedResponse(
            data: PodcastEpisodePubDecorator::getInstance($podcastEpisode),
            cacheSettings: new CacheSettings()
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(
        path: '/{slug}/podcasts/{podcastId}/podcast-episodes',
        name: 'list_by_podcast',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
            'podcast' => Requirement::UUID,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\QueryParameter('podcastId', 'podcastId', description: 'Uuid of the Podcast', schema: new OA\Schema(type: 'string'))]
    #[OA\QueryParameter('page', 'page', schema: new OA\Schema(type: 'integer', default: 1, maximum: ApiPubParams::MAX_PAGE, minimum: 1))]
    #[OA\QueryParameter('limit', 'limit', schema: new OA\Schema(type: 'integer', default: ApiPubParams::LIMIT_DEFAULT, minimum: 1, enum: ApiPubParams::ALLOWED_LIMITS))]
    #[OA\QueryParameter('excludeIds[]', 'excludeIds[]', schema: new OA\Schema(type: 'string', maxItems: ApiPubParams::MAX_EXCLUDED_IDS))]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OAResponse([PodcastEpisodePubDecorator::class])]
    public function getListByPodcast(string $slug, string $podcastId, ApiPubParams $apiParams): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);

        return $this->okCachedResponse(
            data: $this->repositoryDecorator->getListByPodcast(
                publicExport: $publicExport,
                podcast: $this->getPodcast(
                    publicExport: $publicExport,
                    podcastId: $podcastId,
                ),
                apiParams: $apiParams
            ),
            cacheSettings: new CacheSettings()
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(
        path: '/{slug}/podcast-episodes',
        name: 'list',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\QueryParameter('page', 'page', schema: new OA\Schema(type: 'integer', default: 1, maximum: ApiPubParams::MAX_PAGE, minimum: 1))]
    #[OA\QueryParameter('limit', 'limit', schema: new OA\Schema(type: 'integer', default: ApiPubParams::LIMIT_DEFAULT, minimum: 1, enum: ApiPubParams::ALLOWED_LIMITS))]
    #[OA\QueryParameter('excludeIds[]', 'excludeIds[]', schema: new OA\Schema(type: 'string', maxItems: ApiPubParams::MAX_EXCLUDED_IDS))]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OAResponse([PodcastEpisodePubDecorator::class])]
    public function getList(string $slug, ApiPubParams $apiParams): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);

        return $this->okCachedResponse(
            data: $this->repositoryDecorator->getList(
                publicExport: $publicExport,
                apiParams: $apiParams
            ),
            cacheSettings: new CacheSettings()
        );
    }
}
