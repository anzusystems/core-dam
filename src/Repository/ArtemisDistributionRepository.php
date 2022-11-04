<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Repository\AbstractAnzuRepository;
use App\Entity\ArtemisDistribution;

/**
 * @extends AbstractAnzuRepository<ArtemisDistribution>
 *
 * @method ArtemisDistribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtemisDistribution|null findOneBy(array $criteria, array $orderBy = null)
 */
final class ArtemisDistributionRepository extends AbstractAnzuRepository
{
    protected function getEntityClass(): string
    {
        return ArtemisDistribution::class;
    }
}
