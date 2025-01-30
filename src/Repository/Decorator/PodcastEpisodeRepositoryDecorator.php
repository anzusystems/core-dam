<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiInfiniteResponseList;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PodcastEpisode;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use App\App;
use App\Model\Domain\PodcastEpisode\PodcastEpisodePubDecorator;
use App\Model\Request\ApiPubParams;
use App\Repository\PodcastEpisodeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PodcastEpisodeRepositoryDecorator
{
    public function __construct(
        private PodcastEpisodeRepository $podcastEpisodeRepository
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getList(PublicExport $publicExport, Podcast $podcast, ApiPubParams $apiParams): ApiInfiniteResponseList
    {
        if ($podcast->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new NotFoundHttpException('Podcast not found');
        }
        if (false === $publicExport->getType()->isEnabled($podcast)) {
            throw new NotFoundHttpException('Podcast not found');
        }

        $data = $this->podcastEpisodeRepository->getByPublicExport($publicExport, $podcast, $apiParams);

        return (new ApiInfiniteResponseList())
            ->setData($data->map(
                fn (PodcastEpisode $episode): PodcastEpisodePubDecorator => PodcastEpisodePubDecorator::getInstance($episode)
            )->slice(App::ZERO, $apiParams->getLimit()))
            ->setHasNextPage(count($data) > $apiParams->getLimit())
        ;
    }
}
