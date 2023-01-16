<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageFileAttributesDto
{
    #[Serialize]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    private string $partialChecksum = '';

    #[Serialize]
    private string $originFileName = '';

    #[Serialize]
    private string $slugOriginFileName = '';

    #[Serialize]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    private string $mimeType = '';

    #[Serialize]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    private int $size = 0;

    public static function getInstance(ImageFile $imageFile): self
    {
        return (new self())
            ->setPartialChecksum($imageFile->getAssetAttributes()->getChecksum())
            ->setOriginFileName($imageFile->getAssetAttributes()->getOriginFileName())
            ->setSlugOriginFileName($imageFile->getAssetAttributes()->getOriginFileName())
            ->setMimeType($imageFile->getAssetAttributes()->getMimeType())
            ->setSize($imageFile->getAssetAttributes()->getSize())
        ;
    }

    public function getPartialChecksum(): string
    {
        return $this->partialChecksum;
    }

    public function setPartialChecksum(string $partialChecksum): self
    {
        $this->partialChecksum = $partialChecksum;

        return $this;
    }

    public function getOriginFileName(): string
    {
        return $this->originFileName;
    }

    public function setOriginFileName(string $originFileName): self
    {
        $this->originFileName = $originFileName;

        return $this;
    }

    public function getSlugOriginFileName(): string
    {
        return $this->slugOriginFileName;
    }

    public function setSlugOriginFileName(string $slugOriginFileName): self
    {
        $this->slugOriginFileName = $slugOriginFileName;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }
}
