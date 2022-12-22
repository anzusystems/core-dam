<?php

declare(strict_types=1);

namespace App\Model\Domain\AssetLicence;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class AssetLicenceDto
{
    private AssetLicence $assetLicence;

    public static function getInstance(AssetLicence $assetLicence): self
    {
        return (new self())
            ->setAssetLicence($assetLicence)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getAssetLicence(): AssetLicence
    {
        return $this->assetLicence;
    }

    public function setAssetLicence(AssetLicence $assetLicence): self
    {
        $this->assetLicence = $assetLicence;

        return $this;
    }

    #[Serialize]
    public function getName(): string
    {
        return $this->assetLicence->getName();
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getExtSystem(): ExtSystem
    {
        return $this->assetLicence->getExtSystem();
    }
}
