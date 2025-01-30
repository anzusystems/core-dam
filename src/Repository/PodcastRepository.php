<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Helper\CollectionHelper;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Repository\PodcastRepository as BasePodcastRepository;
use App\Model\Request\ApiPubParams;
use App\Repository\Trait\ApiPubParamsTrait;
use App\Repository\Trait\ExportTypeFilterTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Order;

final class PodcastRepository extends BasePodcastRepository
{
    use ExportTypeFilterTrait;
    use ApiPubParamsTrait;

    public function getByPublicExport(
        PublicExport $publicExport,
        ApiPubParams $apiParams
    ): Collection {
        $qb = $this->createQueryBuilder('entity')
            ->where('entity.licence = :licence')
            ->setParameter('licence', $publicExport->getAssetLicence())
        ;

        $this->applyExportType($qb, $publicExport, Order::Ascending);
        $this->applyApiPubParams($qb, $apiParams);

        return CollectionHelper::newCollection(
            $qb->getQuery()->getResult()
        );
    }
}
