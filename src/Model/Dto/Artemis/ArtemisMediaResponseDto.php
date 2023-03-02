<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ArtemisMediaResponseDto
{
    #[Serialize(serializedName: '_meta')]
    private ArtemisMediaMetaDto $meta;

    #[Serialize]
    private ArtemisAudioMediaDto $media;

    public function __construct()
    {
        $this->setMeta(new ArtemisMediaMetaDto());
        $this->setMedia(new ArtemisAudioMediaDto());
    }

    public function getMeta(): ArtemisMediaMetaDto
    {
        return $this->meta;
    }

    public function setMeta(ArtemisMediaMetaDto $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getMedia(): ArtemisAudioMediaDto
    {
        return $this->media;
    }

    public function setMedia(ArtemisAudioMediaDto $media): self
    {
        $this->media = $media;

        return $this;
    }
}
