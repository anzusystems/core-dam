<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageAssetFlagsDto
{
    #[Serialize]
    private bool $isDuplicate = false;

    #[Serialize]
    private bool $isDescribed = false;

    public static function getInstance(ImageFile $imageFile): self
    {
        return (new self())
            ->setIsIsDuplicate($imageFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate))
            ->setIsIsDescribed($imageFile->getAsset()->getAssetFlags()->isDescribed())
        ;
    }

    public function isIsDuplicate(): bool
    {
        return $this->isDuplicate;
    }

    public function setIsIsDuplicate(bool $isDuplicate): self
    {
        $this->isDuplicate = $isDuplicate;

        return $this;
    }

    public function isIsDescribed(): bool
    {
        return $this->isDescribed;
    }

    public function setIsIsDescribed(bool $isDescribed): self
    {
        $this->isDescribed = $isDescribed;

        return $this;
    }
}
