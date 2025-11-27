<?php

declare(strict_types=1);

namespace App\Messenger\Message;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class CacheCdnPurgeMessage
{
    /**
     * @var list<string>
     */
    #[Serialize]
    private array $purgeUrls = [];

    /**
     * @var list<string>
     */
    #[Serialize]
    private array $warmupUrls = [];

    #[Serialize]
    private bool $purgeBotCache = false;

    /**
     * @var list<string>
     */
    #[Serialize]
    private array $facebookScrapeUrls = [];

    /**
     * @param list<string> $urls
     */
    public static function getInstance(array $urls, bool $purgeBotCache = false): self
    {
        return new self()
            ->setPurgeUrls($urls)
            ->setWarmupUrls($urls)
            ->setPurgeBotCache($purgeBotCache)
            ->setFacebookScrapeUrls($urls)
        ;
    }

    /**
     * @return list<string>
     */
    public function getPurgeUrls(): array
    {
        return $this->purgeUrls;
    }

    /**
     * @param list<string> $purgeUrls
     */
    public function setPurgeUrls(array $purgeUrls): self
    {
        $this->purgeUrls = $purgeUrls;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getWarmupUrls(): array
    {
        return $this->warmupUrls;
    }

    /**
     * @param list<string> $warmupUrls
     */
    public function setWarmupUrls(array $warmupUrls): self
    {
        $this->warmupUrls = $warmupUrls;

        return $this;
    }

    public function isPurgeBotCache(): bool
    {
        return $this->purgeBotCache;
    }

    public function setPurgeBotCache(bool $purgeBotCache): self
    {
        $this->purgeBotCache = $purgeBotCache;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getFacebookScrapeUrls(): array
    {
        return $this->facebookScrapeUrls;
    }

    /**
     * @param list<string> $facebookScrapeUrls
     */
    public function setFacebookScrapeUrls(array $facebookScrapeUrls): self
    {
        $this->facebookScrapeUrls = $facebookScrapeUrls;

        return $this;
    }
}
