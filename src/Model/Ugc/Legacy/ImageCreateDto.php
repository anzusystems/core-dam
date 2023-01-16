<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy;

use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Ugc\Legacy\Embeds\ImageFileAttributesDto;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageCreateDto
{
    #[Serialize]
    #[Assert\Valid]
    private ImageFileAttributesDto $fileAttributes;

    public function __construct()
    {
        $this->setFileAttributes(new ImageFileAttributesDto());
    }

    public function getFileAttributes(): ImageFileAttributesDto
    {
        return $this->fileAttributes;
    }

    public function setFileAttributes(ImageFileAttributesDto $fileAttributes): self
    {
        $this->fileAttributes = $fileAttributes;

        return $this;
    }
}
