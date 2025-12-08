<?php

declare(strict_types=1);

namespace App\Cache;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ResetInterface;

#[Autoconfigure(shared: false)]
final class PurgeCacheUrlContainer implements ResetInterface
{
    private const array EMPTY_URLS = [];

    /**
     * A list of URLs to purge represented as an associative array to ensure uniqueness
     * and enable efficient manipulation.
     *
     * @var array<string, true>
     */
    private array $urls = self::EMPTY_URLS;

    /**
     * A list of URLs to warmup after purge represented as an associative array to ensure uniqueness
     * and enable efficient manipulation.
     *
     * @var array<string, true>
     */
    private array $warmupUrls = self::EMPTY_URLS;
    private bool $botCachePurgeEnabled = false;

    /**
     * @return list<string>
     */
    public function getUrls(): array
    {
        return array_keys($this->urls);
    }

    /**
     * @param list<string> $urls
     */
    public function setUrls(array $urls): self
    {
        $this->urls = self::EMPTY_URLS;
        foreach ($urls as $url) {
            $this->urls[$url] = true;
        }

        return $this;
    }

    public function addUrl(string $url): self
    {
        $this->urls[$url] = true;

        return $this;
    }

    /**
     * @param list<string> $urls
     */
    public function addUrls(array $urls): self
    {
        foreach ($urls as $url) {
            $this->urls[$url] = true;
        }

        return $this;
    }

    public function reset(): void
    {
        $this->urls = self::EMPTY_URLS;
        $this->warmupUrls = self::EMPTY_URLS;
        $this->setBotCachePurgeEnabled(false);
    }

    /**
     * @return list<string>
     */
    public function getWarmupUrls(): array
    {
        return array_keys($this->warmupUrls);
    }

    /**
     * @param list<string> $urls
     */
    public function setWarmupUrls(array $urls): self
    {
        $this->warmupUrls = self::EMPTY_URLS;
        foreach ($urls as $url) {
            $this->warmupUrls[$url] = true;
        }

        return $this;
    }

    public function addWarmupUrl(string $url): self
    {
        $this->warmupUrls[$url] = true;

        return $this;
    }

    /**
     * @param list<string> $urls
     */
    public function addWarmupUrls(array $urls): self
    {
        foreach ($urls as $url) {
            $this->warmupUrls[$url] = true;
        }

        return $this;
    }

    public function isBotCachePurgeEnabled(): bool
    {
        return $this->botCachePurgeEnabled;
    }

    public function setBotCachePurgeEnabled(bool $botCachePurgeEnabled): self
    {
        $this->botCachePurgeEnabled = $botCachePurgeEnabled;

        return $this;
    }
}
