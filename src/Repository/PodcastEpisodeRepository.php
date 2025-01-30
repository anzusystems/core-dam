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

    public function getByPublicExport(
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
}
