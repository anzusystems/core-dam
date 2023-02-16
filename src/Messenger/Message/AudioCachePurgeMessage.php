<?php

declare(strict_types=1);

namespace App\Messenger\Message;

final readonly class AudioCachePurgeMessage
{
    public function __construct(
        private string $audioId,
        private string $path,
        private string $extSystemSlug,
    ) {
    }

    public function getAudioId(): string
    {
        return $this->audioId;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getExtSystemSlug(): string
    {
        return $this->extSystemSlug;
    }
}
