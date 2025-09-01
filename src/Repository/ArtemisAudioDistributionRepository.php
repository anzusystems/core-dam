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
    public function findByEpisodeAndAsset(
        string $assetId,
        string $episodeId,
        string $distributionService
    ): ?Distribution {
        return $this->findOneBy([
            'assetId' => $assetId,
            'texts.episodeId' => $episodeId,
            'distributionService' => $distributionService,
        ]);
    }

    protected function getEntityClass(): string
    {
        return ArtemisAudioDistribution::class;
    }
}
