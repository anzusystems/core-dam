<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class AssetFileCmsSysDecorator
{
    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    private AssetFile $assetFile;

    public static function getInstance(AssetFile $assetFile): self
    {
        return (new self())
            ->setAssetFile($assetFile);
    }

    public function getAssetFile(): AssetFile
    {
        return $this->assetFile;
    }

    public function setAssetFile(AssetFile $assetFile): self
    {
        $this->assetFile = $assetFile;

        return $this;
    }

    #[Serialize(handler: EntityIdHandler::class)]
    public function getAnzuDamUuid(): Asset
    {
        return $this->assetFile->getAsset();
    }

    #[Serialize]
    public function getOriginAssetId(): string
    {
        return $this->assetFile->getAssetAttributes()->getOriginAssetId();
    }

    #[Serialize]
    public function getAssetType(): AssetType
    {
        return $this->assetFile->getAsset()->getAssetType();
    }

    #[Serialize]
    public function getStatus(): AssetFileProcessStatus
    {
        return $this->assetFile->getAssetAttributes()->getStatus();
    }
}
