<?php

declare(strict_types=1);

namespace App\Domain\AssetLicence;

use AnzuSystems\CoreDamBundle\Domain\AbstractManager;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use App\Model\Domain\AssetLicence\UpsertAssertLicenceDto;
use AnzuSystems\CoreDamBundle\Domain\AssetLicence\AssetLicenceManager as BaseAssetLicenceManager;

final class AssetLicenceManager extends AbstractManager
{
    public function __construct(
        private readonly BaseAssetLicenceManager $assetLicenceManager,
    ) {
    }

    public function createByDto(UpsertAssertLicenceDto $upsertAssertLicenceDto, bool $flush = true): AssetLicence
    {
        $licence = (new AssetLicence())
            ->setExtSystem($upsertAssertLicenceDto->getExtSystem())
            ->setExtId($upsertAssertLicenceDto->getExtId())
            ->setLimitedFiles($upsertAssertLicenceDto->isLimited())
        ;
        $this->assetLicenceManager->create($licence, false);
        $this->assignLicenceToUsersByLicence($licence, $upsertAssertLicenceDto);
        $this->flush($flush);

        return $licence;
    }

    public function updateByDto(
        AssetLicence $licence,
        UpsertAssertLicenceDto $upsertAssertLicenceDto,
        bool $flush = true,
    ): AssetLicence {
        $this->trackModification($licence);
        $licence->setLimitedFiles($upsertAssertLicenceDto->isLimited());
        $this->assignLicenceToUsersByLicence($licence, $upsertAssertLicenceDto);
        $this->flush($flush);

        return $licence;
    }

    private function assignLicenceToUsersByLicence(
        AssetLicence $licence,
        UpsertAssertLicenceDto $upsertAssertLicenceDto,
    ): void {
        foreach ($upsertAssertLicenceDto->getUsers() as $user) {
            if (null === $user->getSelectedLicence()) {
                $user->setSelectedLicence($licence);
            }
            if (false === $user->getAssetLicences()->containsKey((int) $licence->getId())) {
                $user->getAssetLicences()->set((int) $licence->getId(), $licence);
            }
        }
    }
}
