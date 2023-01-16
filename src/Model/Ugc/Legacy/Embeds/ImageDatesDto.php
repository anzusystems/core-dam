<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy\Embeds;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use DateTimeImmutable;

final class ImageDatesDto
{
    #[Serialize]
    private DateTimeImmutable $uploadedAt;

    public static function getInstance(ImageFile $imageFile): self
    {
        return (new self())
            ->setUploadedAt($imageFile->getAsset()->getDates()->getUploadedAt())
        ;
    }

    public function getUploadedAt(): DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(DateTimeImmutable $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }
}
