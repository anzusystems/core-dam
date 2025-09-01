<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiInfiniteResponseList;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use App\App;
use App\Domain\PodcastEpisode\PodcastEpisodePubBuilder;
use App\Model\Domain\PodcastEpisode\PodcastEpisodeLatestPubDecorator;
use App\Model\Request\ApiPubParams;
use App\Repository\PodcastEpisodeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PodcastEpisodeRepositoryDecorator
{
    public function __construct(
        private PodcastEpisodeRepository $podcastEpisodeRepository,
        private PodcastEpisodePubBuilder $podcastEpisodePubBuilder,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getListByPodcast(PublicExport $publicExport, Podcast $podcast, ApiPubParams $apiParams): ApiInfiniteResponseList
    {
        if ($podcast->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new NotFoundHttpException('Podcast not found');
        }
        if (false === $publicExport->getType()->isEnabled($podcast)) {
            throw new NotFoundHttpException('Podcast not found');
        }

        $data = $this->podcastEpisodeRepository->getByPublicExportAndPodcast($publicExport, $podcast, $apiParams);

        return (new ApiInfiniteResponseList())
            ->setData($data->map(
                fn (PodcastEpisode $episode): PodcastEpisodeLatestPubDecorator => $this->podcastEpisodePubBuilder->buildPodcastEpisodePubDecorator($episode)
            )->slice(App::ZERO, $apiParams->getLimit()))
            ->setHasNextPage(count($data) > $apiParams->getLimit())
        ;
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getList(PublicExport $publicExport, ApiPubParams $apiParams): ApiInfiniteResponseList
    {
        $data = $this->podcastEpisodeRepository->getByPublicExport($publicExport, $apiParams);

        return (new ApiInfiniteResponseList())
            ->setData($data->map(
                fn (PodcastEpisode $episode): PodcastEpisodeLatestPubDecorator => $this->podcastEpisodePubBuilder->buildPodcastEpisodePubDecorator($episode)
            )->slice(App::ZERO, $apiParams->getLimit()))
            ->setHasNextPage(count($data) > $apiParams->getLimit())
        ;
    }
}
