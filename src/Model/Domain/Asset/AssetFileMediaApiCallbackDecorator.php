<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class AssetFileMediaApiCallbackDecorator
{
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

    #[Serialize]
    public function getAnzuDamUuid(): string
    {
        return $this->assetFile->getAssetAttributes()->getOriginAssetId();
    }
}
