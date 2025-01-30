<?php

declare(strict_types=1);

namespace App\Controller\Api\Pub\Traits;

use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use App\Exception\PubNotFoundHttpException;
use App\Repository\VideoShowRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait VideoShowTrait
{
    protected VideoShowRepository $videoShowRepository;

    #[Required]
    public function setVideoShowRepository(VideoShowRepository $videoShowRepository): void
    {
        $this->videoShowRepository = $videoShowRepository;
    }

    public function getVideoShow(PublicExport $publicExport, string $videoShowId): VideoShow
    {
        $videoShow = $this->videoShowRepository->find($videoShowId);
        if (false === $videoShow instanceof VideoShow) {
            throw new PubNotFoundHttpException('VideoShow not found');
        }
        if ($videoShow->getLicence()->isNot($publicExport->getAssetLicence())) {
            throw new PubNotFoundHttpException('VideoShow not found');
        }

        return $videoShow;
    }
}
