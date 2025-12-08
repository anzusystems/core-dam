<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiInfiniteResponseList;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use App\App;
use App\Model\Domain\Podcast\PodcastPubDecorator;
use App\Model\Request\ApiPubParams;
use App\Repository\PodcastRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class PodcastRepositoryDecorator
{
    public function __construct(
        private PodcastRepository $podcastRepository
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getList(PublicExport $publicExport, ApiPubParams $apiParams): ApiInfiniteResponseList
    {
        $data = $this->podcastRepository->getByPublicExport($publicExport, $apiParams);

        return (new ApiInfiniteResponseList())
            ->setData($data->map(
                fn (Podcast $podcast): PodcastPubDecorator => PodcastPubDecorator::getInstance(
                    podcast: $podcast,
                    publicExport: $publicExport
                )
            )->slice(App::ZERO, $apiParams->getLimit()))
            ->setHasNextPage(count($data) > $apiParams->getLimit())
        ;
    }
}
