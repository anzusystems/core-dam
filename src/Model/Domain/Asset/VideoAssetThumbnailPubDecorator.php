<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Serializer\Handler\Handlers\PublicLinksTagCollectionHandler;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class VideoAssetThumbnailPubDecorator extends AbstractAssetThumbnailPubDecorator
{
    #[Serialize(handler: PublicLinksTagCollectionHandler::class, type: 'video_thumbnail')]
    public function getLinks(): ?ImageFile
    {
        return $this->getImageFile();
    }
}
