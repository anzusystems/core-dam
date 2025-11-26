<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CoreDamBundle\Entity\Distribution;
use AnzuSystems\CoreDamBundle\Repository\DistributionRepository;
use App\Entity\ArtemisAudioDistribution;

/**
 * @method ArtemisAudioDistribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtemisAudioDistribution|null findOneBy(array $criteria, array $orderBy = null)
 */
final class ArtemisAudioDistributionRepository extends DistributionRepository
{
    protected function getEntityClass(): string
    {
        return ArtemisAudioDistribution::class;
    }
}
