<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageAttributesDto
{
    #[Serialize]
    private int $rationWidth = 0;

    #[Serialize]
    private int $rationHeight = 0;

    #[Serialize]
    private int $width = 0;

    #[Serialize]
    private int $height = 0;

    #[Serialize]
    private int $rotation = 0;

    public static function getInstance(ImageFile $imageFile): self
    {
        return (new self())
            ->setRationWidth($imageFile->getImageAttributes()->getRatioWidth())
            ->setRationHeight($imageFile->getImageAttributes()->getRatioHeight())
            ->setWidth($imageFile->getImageAttributes()->getWidth())
            ->setHeight($imageFile->getImageAttributes()->getHeight())
            ->setRotation($imageFile->getImageAttributes()->getRotation())
        ;
    }

    public function getRationWidth(): int
    {
        return $this->rationWidth;
    }

    public function setRationWidth(int $rationWidth): self
    {
        $this->rationWidth = $rationWidth;

        return $this;
    }

    public function getRationHeight(): int
    {
        return $this->rationHeight;
    }

    public function setRationHeight(int $rationHeight): self
    {
        $this->rationHeight = $rationHeight;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getRotation(): int
    {
        return $this->rotation;
    }

    public function setRotation(int $rotation): self
    {
        $this->rotation = $rotation;

        return $this;
    }
}
