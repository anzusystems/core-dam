<?php

declare(strict_types=1);

namespace App\Model\Domain\AssetLicenceGroup;

use AnzuSystems\CoreDamBundle\Entity\AssetLicenceGroup;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;
use Doctrine\Common\Collections\Collection;

final class AssetLicenceGroupDto
{
    private AssetLicenceGroup $assetLicenceGroup;

    public static function getInstance(AssetLicenceGroup $assetLicenceGroup): self
    {
        return (new self())
            ->setAssetLicenceGroup($assetLicenceGroup)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getAssetLicenceGroup(): AssetLicenceGroup
    {
        return $this->assetLicenceGroup;
    }

    public function setAssetLicenceGroup(AssetLicenceGroup $assetLicenceGroup): self
    {
        $this->assetLicenceGroup = $assetLicenceGroup;
        return $this;
    }

    #[Serialize]
    public function getName(): string
    {
        return $this->assetLicenceGroup->getName();
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getExtSystem(): ExtSystem
    {
        return $this->assetLicenceGroup->getExtSystem();
    }

    #[Serialize(handler: EntityIdHandler::class, type: AssetLicenceGroup::class)]
    public function getLicences(): Collection
    {
        return $this->assetLicenceGroup->getLicences();
    }
}
