<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Helper\CollectionHelper;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Repository\PodcastEpisodeRepository as BasePodcastEpisodeRepository;
use App\Model\Request\ApiPubParams;
use App\Repository\Trait\ApiPubParamsTrait;
use App\Repository\Trait\ExportTypeFilterTrait;
use Doctrine\Common\Collections\Collection;

final class PodcastEpisodeRepository extends BasePodcastEpisodeRepository
{
    use ExportTypeFilterTrait;
    use ApiPubParamsTrait;

    public function getByPublicExportAndPodcast(
        PublicExport $publicExport,
        Podcast $podcast,
        ApiPubParams $apiParams,
    ): Collection {
        $qb = $this->createQueryBuilder('entity')
            ->where('IDENTITY(entity.asset) IS NOT NULL')
            ->andWhere('IDENTITY(entity.podcast) = :podcast')
            ->setParameter('podcast', (string) $podcast->getId())
        ;

        $this->applyExportType($qb, $publicExport);
        $this->applyApiPubParams($qb, $apiParams);

        return CollectionHelper::newCollection(
            $qb->getQuery()->getResult()
        );
    }

    public function getByPublicExport(
        PublicExport $publicExport,
        ApiPubParams $apiParams,
    ): Collection {
        $qb = $this->createQueryBuilder('entity')
            ->where('IDENTITY(entity.asset) IS NOT NULL')
            ->innerJoin('entity.podcast', 'podcast')
            ->where('entity.licence = :licence')
            ->setParameter('licence', $publicExport->getAssetLicence())
            ->addOrderBy('entity.dates.publicationDate', 'DESC')
        ;

        $this->applyExportTypeEnable($qb, $publicExport);
        $this->applyExportTypeEnable($qb, $publicExport, 'podcast');
        $this->applyApiPubParams($qb, $apiParams);

        return CollectionHelper::newCollection(
            $qb->getQuery()->getResult()
        );
    }

}
