<?php

declare(strict_types=1);


namespace App\Model\Domain\AssetFile;

use Anzu\CommonBundle\AnzuSerializer\Attributes\AnzuSerialize;
use Anzu\CommonBundle\AnzuSerializer\Handler\Handlers\IdentifiableHandler;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;

final class AssetFileAdmNotificationDecorator
{
    #[AnzuSerialize(serializedName: 'id', handler: IdentifiableHandler::class)]
    private AssetFile $assetFile;

    public static function getInstance(AssetFile $assetFile): self
    {
        return (new self())
            ->setAssetFile($assetFile);
    }

    public function setAssetFile(AssetFile $assetFile): self
    {
        $this->assetFile = $assetFile;

        return $this;
    }

    public function getAssetFile(): AssetFile
    {
        return $this->assetFile;
    }

    #[AnzuSerialize]
    public function getStatus(): AssetFileProcessStatus
    {
        return $this->assetFile->getAssetAttributes()->getStatus();
    }
}