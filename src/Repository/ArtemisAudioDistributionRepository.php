<?php

declare(strict_types=1);

namespace App\Repository;

use AnzuSystems\CommonBundle\Repository\AbstractAnzuRepository;
use App\Entity\ArtemisAudioDistribution;

/**
 * @extends AbstractAnzuRepository<ArtemisAudioDistribution>
 *
 * @method ArtemisAudioDistribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArtemisAudioDistribution|null findOneBy(array $criteria, array $orderBy = null)
 */
final class ArtemisAudioDistributionRepository extends AbstractAnzuRepository
{
    protected function getEntityClass(): string
    {
        return ArtemisAudioDistribution::class;
    }
}
