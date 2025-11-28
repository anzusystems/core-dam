<?php

declare(strict_types=1);

namespace App\Cache;

use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use App\App;
use App\Messenger\Message\CacheCdnPurgeMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final class CacheCdnPurger
{
    private const int MAX_CDN_PATHS = 30;

    /**
     * @param array<string, PurgeCacheTagContainer> $tagContainers
     */
    public function __construct(
        #[Autowire(env: 'CORE_DAM_CDN_PURGE_ENABLED')]
        private readonly bool $cdnPurgeEnabled,
        #[Autowire(env: 'CACHE_PURGE_DEBUG_ENABLED')]
        private readonly bool $debugEnabled,
        private readonly MessageBusInterface $messenger,
        private readonly PurgeCacheUrlContainer $purgeCacheUrlContainer,
        private readonly DamLogger $damLogger,
        private array $tagContainers = [],
    ) {
    }

    public function addTag(string $domain, string $tag): void
    {
        if (false === isset($this->tagContainers[$domain])) {
            $this->tagContainers[$domain] = new PurgeCacheTagContainer();
        }

        $this->tagContainers[$domain]->addTag($tag);
    }

    /**
     * @param string|list<string> $urls
     */
    public function addUrl(string|array $urls): self
    {
        $this->purgeCacheUrlContainer->addUrls((array) $urls);

        return $this;
    }

    /**
     * The specified URL will be purged from the CDN cache (with bot cache), and then the Purger will re-fetch it
     * to ensure it is available in the cache. We will also submit a request to Facebook to scrape the content of the URL.
     *
     * @param string|list<string> $urls
     */
    public function addExtendedUrl(string|array $urls): self
    {
        $this->purgeCacheUrlContainer->addUrls((array) $urls);
        $this->purgeCacheUrlContainer->addWarmupUrls((array) $urls);
        $this->purgeCacheUrlContainer->setBotCachePurgeEnabled(true);

        return $this;
    }

    public function purge(): void
    {
        $urls = $this->purgeCacheUrlContainer->getUrls();

        if (empty($urls)) {
            return;
        }

        count($urls) > self::MAX_CDN_PATHS
            ? $this->dispatchInBatch($urls)
            : $this->dispatchPurgeCacheUrlContainer($urls)
        ;

        $this->purgeCacheUrlContainer->reset();

        foreach ($this->tagContainers as $domain => $tagContainer) {
            $urls = $tagContainer->getTags();
            if (empty($urls)) {
                continue;
            }

            if ($this->debugEnabled) {
                $this->damLogger->info('CDN purge', 'Purge CDN tags', [
                    'domain' => $domain,
                    'tags' => $urls,
                ]);
            }

            $tagContainer->reset();
        }

        $this->tagContainers = [];
    }

    private function dispatchPurgeCacheUrlContainer(array $urls): void
    {
        $purgeMessage = new CacheCdnPurgeMessage();
        $purgeMessage->setPurgeUrls($urls);
        $purgeMessage->setWarmupUrls($this->purgeCacheUrlContainer->getWarmupUrls());
        $purgeMessage->setPurgeBotCache($this->purgeCacheUrlContainer->isBotCachePurgeEnabled());

        $this->dispatchCdnPurge($purgeMessage);
    }

    private function dispatchInBatch(array $urls): void
    {
        $count = count($urls);
        for ($i = App::ZERO; $i < $count; $i += self::MAX_CDN_PATHS) {
            $pathsBlock = array_slice($urls, $i, self::MAX_CDN_PATHS);
            $purgeMessage = new CacheCdnPurgeMessage();
            $purgeMessage->setPurgeUrls($pathsBlock);

            $this->dispatchCdnPurge($purgeMessage);
        }
    }

    private function dispatchCdnPurge(CacheCdnPurgeMessage $purgeMessage): void
    {
        if ($this->cdnPurgeEnabled) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->messenger->dispatch($purgeMessage);
        }
        if ($this->debugEnabled) {
            $this->damLogger->info('CDN purge', 'Purge CDN urls', [
                'purgeUrls' => $purgeMessage->getPurgeUrls(),
                'warmupUrls' => $purgeMessage->getWarmupUrls(),
                'purgeBotCache' => $purgeMessage->isPurgeBotCache(),
            ]);
        }
    }
}
