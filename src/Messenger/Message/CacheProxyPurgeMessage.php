<?php

declare(strict_types=1);

namespace App\Messenger\Message;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class CacheProxyPurgeMessage
{
    #[Serialize]
    private string $url;

    #[Serialize(serializedName: 'xkey')]
    private ?string $xKey;

    #[Serialize]
    private bool $hard = false;

    public static function getInstance(string $url, ?string $xKey = null, bool $hard = false): self
    {
        return new self()
            ->setUrl($url)
            ->setXKey($xKey)
            ->setHard($hard)
        ;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getXKey(): ?string
    {
        return $this->xKey;
    }

    public function setXKey(?string $xKey): self
    {
        $this->xKey = $xKey;

        return $this;
    }

    public function isHard(): bool
    {
        return $this->hard;
    }

    public function setHard(bool $hard): self
    {
        $this->hard = $hard;

        return $this;
    }
}
