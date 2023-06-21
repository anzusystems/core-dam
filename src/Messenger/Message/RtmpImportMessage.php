<?php

declare(strict_types=1);

namespace App\Messenger\Message;

final class RtmpImportMessage
{
    public function __construct(
        private string $path
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
