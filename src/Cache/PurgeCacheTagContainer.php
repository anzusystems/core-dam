<?php

declare(strict_types=1);

namespace App\Cache;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ResetInterface;

#[Autoconfigure(shared: false)]
final class PurgeCacheTagContainer implements ResetInterface
{
    private const array EMPTY_TAGS = [];

    /**
     * @var list<string>
     */
    private array $tags = self::EMPTY_TAGS;

    /**
     * @return list<string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param list<string> $tags
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function addTag(string $tag): self
    {
        if (false === in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function reset(): void
    {
        $this->tags = self::EMPTY_TAGS;
    }
}
