<?php

declare(strict_types=1);

namespace App\Model\Ugc\Legacy;

use AnzuSystems\CoreDamBundle\Entity\Chunk;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\Model\Ugc\Legacy\Embeds\ImageTagsDto;

final class ChunkCreatedDto
{
    private Chunk $chunk;

    public static function getInstance(Chunk $chunk): self
    {
        return (new self())
            ->setChunk($chunk)
        ;
    }

    public function getChunk(): Chunk
    {
        return $this->chunk;
    }

    public function setChunk(Chunk $chunk): self
    {
        $this->chunk = $chunk;

        return $this;
    }

    #[Serialize]
    public function getAssetTags(): ImageTagsDto
    {
        /** @var ImageFile $imageFile */
        $imageFile = $this->chunk->getAssetFile();

        return ImageTagsDto::getInstance($imageFile);
    }

    #[Serialize]
    public function getOffset(): int
    {
        return $this->chunk->getOffset();
    }
}
