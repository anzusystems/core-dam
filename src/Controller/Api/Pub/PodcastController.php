<?php

declare(strict_types=1);

namespace App\Controller\Api\Pub;

use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use App\Controller\AbstractApiPubController;
use App\Controller\Api\Pub\Traits\PodcastTrait;
use App\Exception\PubNotFoundHttpException;
use App\Model\Domain\Podcast\PodcastPubDecorator;
use App\Model\Request\ApiPubParams;
use App\Model\Request\CacheSettings;
use App\Repository\Decorator\PodcastRepositoryDecorator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[Route(path: '', name: 'pub_podcast_v1_')]
#[OA\Tag('Podcast')]
final class PodcastController extends AbstractApiPubController
{
    use PodcastTrait;

    public function __construct(
        private readonly PodcastRepositoryDecorator $repositoryDecorator,
    ) {
    }

    /**
     * @throws PubNotFoundHttpException
     */
    #[Route(
        path: '/{slug}/podcasts/{podcastId}',
        name: 'get_one',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
            'podcastId' => Requirement::UUID,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OA\QueryParameter('podcastId', 'podcastId', description: 'Uuid of the Podcast', schema: new OA\Schema(type: 'string'))]
    #[OAResponse(PodcastPubDecorator::class)]
    public function getOne(string $slug, string $podcastId): JsonResponse
    {
        $publicExport = $this->getPublicExportBySlug($slug);

        return $this->okCachedResponse(
            data: PodcastPubDecorator::getInstance(
                podcast: $this->getPodcast(
                    publicExport: $publicExport,
                    podcastId: $podcastId,
                ),
                publicExport: $publicExport
            ),
            cacheSettings: new CacheSettings()
        );
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(
        path: '/{slug}/podcasts',
        name: 'get_list',
        requirements: [
            'slug' => Requirement::ASCII_SLUG,
        ],
        methods: [Request::METHOD_GET]
    )]
    #[OA\QueryParameter('page', 'page', schema: new OA\Schema(type: 'integer', default: 1, maximum: ApiPubParams::MAX_PAGE, minimum: 1))]
    #[OA\QueryParameter('limit', 'limit', schema: new OA\Schema(type: 'integer', default: ApiPubParams::LIMIT_DEFAULT, minimum: 1, enum: ApiPubParams::ALLOWED_LIMITS))]
    #[OA\QueryParameter('excludeIds[]', 'excludeIds[]', schema: new OA\Schema(type: 'string', maxItems: ApiPubParams::MAX_EXCLUDED_IDS))]
    #[OA\PathParameter('slug', 'slug', description: 'PublicExport slug', schema: new OA\Schema(type: 'string'))]
    #[OAResponse([PodcastPubDecorator::class])]
    public function getList(string $slug, ApiPubParams $apiParams): JsonResponse
    {
        return $this->okCachedResponse(
            data: $this->repositoryDecorator->getList(
                publicExport: $this->getPublicExportBySlug($slug),
                apiParams: $apiParams
            ),
            cacheSettings: new CacheSettings()
        );
    }
}
