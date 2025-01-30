<?php

declare(strict_types=1);

namespace App\Model\Domain\Asset;

use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class AudioAssetMediaPubDecorator
{
    private string $type = '';
    private ?string $mediaUrl = null;
    private ?string $linkUrl = null;
    private ?AudioFile $audioFile = null;

    public static function getInstance(
        string $type,
        ?AudioFile $audioFile = null,
        ?string $mediaUrl = null,
        ?string $linkUrl = null,
    ): self {
        return (new self())
            ->setType($type)
            ->setMediaUrl($mediaUrl)
            ->setLinkUrl($linkUrl)
            ->setAudioFile($audioFile)
        ;
    }

    public function getAudioFile(): ?AudioFile
    {
        return $this->audioFile;
    }

    public function setAudioFile(?AudioFile $audioFile): self
    {
        $this->audioFile = $audioFile;

        return $this;
    }

    #[Serialize]
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    #[Serialize]
    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): self
    {
        $this->mediaUrl = $mediaUrl;

        return $this;
    }

    #[Serialize]
    public function getLinkUrl(): ?string
    {
        return $this->linkUrl;
    }

    public function setLinkUrl(?string $linkUrl): self
    {
        $this->linkUrl = $linkUrl;

        return $this;
    }

    #[Serialize]
    public function getDuration(): ?int
    {
        return $this->audioFile?->getAttributes()->getDuration();
    }
}
