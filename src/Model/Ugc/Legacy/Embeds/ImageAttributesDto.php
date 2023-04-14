<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ImageAttributesDto
{
    #[Serialize]
    private int $ratioWidth = 0;

    #[Serialize]
    private int $ratioHeight = 0;

    #[Serialize]
    private int $width = 0;

    #[Serialize]
    private int $height = 0;

    #[Serialize]
    private int $rotation = 0;

    public static function getInstance(ImageFile $imageFile): self
    {
        return (new self())
            ->setRatioWidth($imageFile->getImageAttributes()->getRatioWidth())
            ->setRatioHeight($imageFile->getImageAttributes()->getRatioHeight())
            ->setWidth($imageFile->getImageAttributes()->getWidth())
            ->setHeight($imageFile->getImageAttributes()->getHeight())
            ->setRotation($imageFile->getImageAttributes()->getRotation())
        ;
    }

    public function getRatioWidth(): int
    {
        return $this->ratioWidth;
    }

    public function setRatioWidth(int $ratioWidth): self
    {
        $this->ratioWidth = $ratioWidth;

        return $this;
    }

    public function getRatioHeight(): int
    {
        return $this->ratioHeight;
    }

    public function setRatioHeight(int $ratioHeight): self
    {
        $this->ratioHeight = $ratioHeight;

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
