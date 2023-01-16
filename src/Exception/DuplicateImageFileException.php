<?php

declare(strict_types=1);

namespace App\Exception;

use AnzuSystems\Contracts\Exception\AnzuException;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;

final class DuplicateImageFileException extends AnzuException
{
    private ImageFile $imageFile;

    public function __construct(ImageFile $imageFile)
    {
        $this->imageFile = $imageFile;
        parent::__construct();
    }

    public function getImageFile(): ImageFile
    {
        return $this->imageFile;
    }
}
