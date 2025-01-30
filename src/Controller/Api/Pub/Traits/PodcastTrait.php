<?php

declare(strict_types=1);

namespace App\Controller\Api\Pub\Traits;

use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use App\Exception\PubNotFoundHttpException;
use App\Repository\PodcastRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait PodcastTrait
{
    protected PodcastRepository $podcastRepository;

    #[Required]
    public function setPodcastRepository(PodcastRepository $podcastRepository): void
    {
        $this->podcastRepository = $podcastRepository;
    }

    public function getPodcast(PublicExport $publicExport, string $podcastId): Podcast
    {
        $podcast = $this->podcastRepository->find($podcastId);
        if (false === $podcast instanceof Podcast) {
            throw new PubNotFoundHttpException('Podcast not found');
        }
        if ($podcast->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new PubNotFoundHttpException('Podcast not found');
        }

        return $podcast;
    }
}
