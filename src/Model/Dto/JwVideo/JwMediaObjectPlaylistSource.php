<?php

declare(strict_types=1);

namespace App\Model\Dto\JwVideo;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class JwMediaObjectPlaylistSource
{
    #[Serialize]
    private string $type = '';

    #[Serialize(serializedName: 'filesize')]
    private ?int $fileSize = null;

    #[Serialize]
    private string $file = '';

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;
        return $this;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }
}
