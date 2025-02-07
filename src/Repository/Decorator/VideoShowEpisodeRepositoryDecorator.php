<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiInfiniteResponseList;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use AnzuSystems\CoreDamBundle\Entity\VideoShowEpisode;
use App\App;
use App\Model\Domain\VideoShowEpisode\VideoShowEpisodePubDecorator;
use App\Model\Request\ApiPubParams;
use App\Repository\VideoShowEpisodeRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class VideoShowEpisodeRepositoryDecorator
{
    public function __construct(
        private VideoShowEpisodeRepository $videoShowEpisodeRepository,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getListByVideoShow(PublicExport $publicExport, VideoShow $videoShow, ApiPubParams $apiParams): ApiInfiniteResponseList
    {
        if ($videoShow->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new NotFoundHttpException('VideoShow not found');
        }
        if (false === $publicExport->getType()->isEnabled($videoShow)) {
            throw new NotFoundHttpException('VideoShow not found');
        }

        $data = $this->videoShowEpisodeRepository->getByPublicExportAndVideoShow($publicExport, $videoShow, $apiParams);

        return (new ApiInfiniteResponseList())
            ->setData($data->map(
                fn (VideoShowEpisode $episode): VideoShowEpisodePubDecorator => VideoShowEpisodePubDecorator::getInstance($episode)
            )->slice(App::ZERO, $apiParams->getLimit()))
            ->setHasNextPage(count($data) > $apiParams->getPage())
        ;
    }
}
