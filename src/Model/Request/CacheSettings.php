<?php

declare(strict_types=1);

namespace App\Model\Request;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final readonly class CacheSettings
{
    private const int DEFAULT_PRIVATE_TTL = 60;
    private const int DEFAULT_PUBLIC_TTL = 30;

    /**
     * @param list<string> $tags
     */
    public function __construct(
        private int $privateTtl = self::DEFAULT_PRIVATE_TTL,
        private int $publicTtl = self::DEFAULT_PUBLIC_TTL,
        private array $tags = [],
    ) {
    }

    #[Serialize]
    public function getPrivateTtl(): int
    {
        return $this->privateTtl;
    }

    #[Serialize]
    public function getPublicTtl(): int
    {
        return $this->publicTtl;
    }

    #[Serialize]
    public function getTags(): array
    {
        return array_values(array_unique($this->tags));
    }

    public function getTagsString(): string
    {
        return implode(' ', $this->getTags());
    }
}
