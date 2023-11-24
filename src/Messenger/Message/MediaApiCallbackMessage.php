<?php

declare(strict_types=1);

namespace App\Messenger\Message;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;

final readonly class MediaApiCallbackMessage
{
    private string $imageId;

    public function __construct(
        private ImageFile $imageFile
    ) {
        $this->imageId = (string) $this->imageFile->getId();
    }

    public function getImageId(): string
    {
        return $this->imageId;
    }
}
