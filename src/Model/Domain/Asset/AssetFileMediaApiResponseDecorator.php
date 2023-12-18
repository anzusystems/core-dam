<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class AssetFileMediaApiResponseDecorator
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

    #[Serialize]
    public function getStatus(): AssetFileProcessStatus
    {
        return $this->assetFile->getAssetAttributes()->getStatus();
    }

    #[Serialize]
    public function getLicenceId(): int
    {
        return (int) $this->assetFile->getLicence()->getId();
    }
}
