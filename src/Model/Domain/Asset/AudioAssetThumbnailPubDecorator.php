<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Serializer\Handler\Handlers\PublicLinksTagCollectionHandler;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class AudioAssetThumbnailPubDecorator
{
    private ?AudioFile $imageFile = null;

    public static function getInstance(?AudioFile $imageFile): self
    {
        return (new self())
            ->setImageFile($imageFile)
        ;
    }

    public function setImageFile(?AudioFile $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }
    public function getImageFile(): ?AudioFile
    {
        return $this->imageFile;
    }

    #[Serialize(handler: PublicLinksTagCollectionHandler::class, type: 'audio_thumbnail')]
    public function getLinks(): ?AudioFile
    {
        return $this->getImageFile();
    }
}
