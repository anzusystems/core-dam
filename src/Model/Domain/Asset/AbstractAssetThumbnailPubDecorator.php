<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;

abstract class AbstractAssetThumbnailPubDecorator
{
    private ?ImageFile $imageFile = null;

    public static function getInstance(?ImageFile $imageFile): static
    {
        $instance = new static();
        $instance->setImageFile($imageFile);
        return $instance;
    }

    public function setImageFile(?ImageFile $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }
    public function getImageFile(): ?ImageFile
    {
        return $this->imageFile;
    }
}
