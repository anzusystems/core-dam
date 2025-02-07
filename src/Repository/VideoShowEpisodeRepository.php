<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Helper\CollectionHelper;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use AnzuSystems\CoreDamBundle\Repository\VideoShowEpisodeRepository as BaseVideoShowEpisodeRepository;
use App\App;
use App\Model\Request\ApiPubParams;
use App\Repository\Trait\ApiPubParamsTrait;
use App\Repository\Trait\ExportTypeFilterTrait;
use Doctrine\Common\Collections\Collection;

final class VideoShowEpisodeRepository extends BaseVideoShowEpisodeRepository
{
    use ExportTypeFilterTrait;
    use ApiPubParamsTrait;

    public function getByPublicExportAndVideoShow(
        PublicExport $publicExport,
        VideoShow $videoShow,
        ApiPubParams $apiParams,
    ): Collection {
        $qb = $this->createQueryBuilder('entity')
            ->where('IDENTITY(entity.asset) IS NOT NULL')
            ->andWhere('IDENTITY(entity.videoShow) = :videoShow')
            ->andWhere('entity.dates.publicationDate <= :now')
            ->setParameter('videoShow', (string) $videoShow->getId())
            ->setParameter('now', App::getAppDate())
        ;

        $this->applyExportType($qb, $publicExport);
        $this->applyApiPubParams($qb, $apiParams);

        return CollectionHelper::newCollection(
            $qb->getQuery()->getResult()
        );
    }
}
