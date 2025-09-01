<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\Entity\ArtemisVideoDistribution;

/**
 * @method ArtemisVideoDistribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtemisVideoDistribution|null findOneBy(array $criteria, array $orderBy = null)
 */
final class ArtemisVideoDistributionRepository extends DistributionRepository
{
    protected function getEntityClass(): string
    {
        return ArtemisVideoDistribution::class;
    }
}
