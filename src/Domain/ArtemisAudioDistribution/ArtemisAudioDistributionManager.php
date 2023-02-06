<?php

declare(strict_types=1);

namespace App\Domain\ArtemisAudioDistribution;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Domain\AssetLicence\AssetLicenceManager as BaseAssetLicenceManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\Author;
use App\Entity\ArtemisAudioDistribution;
use App\Model\Domain\AssetLicence\UpsertAssertLicenceDto;

final class ArtemisAudioDistributionManager extends AbstractManager
{
    public function __construct(
    ) {
    }

    public function create(ArtemisAudioDistribution $distribution, bool $flush = true): ArtemisAudioDistribution
    {
        $this->trackCreation($distribution);
        $this->entityManager->persist($distribution);
        $this->flush($flush);

        return $distribution;
    }

    public function delete(ArtemisAudioDistribution $distribution, bool $flush = true): bool
    {
        $this->entityManager->remove($distribution);
        $this->flush($flush);

        return true;
    }
}
