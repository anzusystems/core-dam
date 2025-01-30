<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Serializer\Handler\Handlers\PublicLinksTagCollectionHandler;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class AudioImageThumbnailPubDecorator
{
    private ?ImageFile $imageFile = null;

    public static function getInstance(?ImageFile $imageFile): self
    {
        return (new self())
            ->setImageFile($imageFile)
        ;
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

    #[Serialize(handler: PublicLinksTagCollectionHandler::class, type: 'audio_thumbnail')]
    public function getLinks(): ?ImageFile
    {
        return $this->getImageFile();
    }
}
