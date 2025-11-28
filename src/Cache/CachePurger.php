<?php

declare(strict_types=1);

namespace App\Cache;

use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use App\Messenger\Message\CacheProxyPurgeMessage;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CachePurger
{
    public function __construct(
        #[Autowire(env: 'CORE_DAM_CACHE_PURGE_HOST')]
        private string $cacheHost,
        #[Autowire(env: 'CORE_DAM_CACHE_PROXY_PURGE_ENABLED')]
        private bool $cacheProxyPurgeEnabled,
        #[Autowire(env: 'CACHE_PURGE_DEBUG_ENABLED')]
        private bool $debugEnabled,
        private MessageBusInterface $messenger,
        private DamLogger $damLogger,
        private PurgeCacheTagContainer $hardPurgeCacheTagContainer,
        private PurgeCacheTagContainer $softPurgeCacheTagContainer,
    ) {
    }

    public function addTag(string|array $tags, bool $hard = false): self
    {
        foreach ((array) $tags as $tag) {
            match ($hard) {
                true => $this->hardPurgeCacheTagContainer->addTag($tag),
                false => $this->softPurgeCacheTagContainer->addTag($tag),
            };
        }

        return $this;
    }

    public function purge(): void
    {
        $uniqueHardPurgeTags = $this->hardPurgeCacheTagContainer->getTags();
        if ($uniqueHardPurgeTags) {
            $purgeMessage = new CacheProxyPurgeMessage();
            $purgeMessage
                ->setUrl($this->cacheHost)
                ->setXKey(implode(' ', $uniqueHardPurgeTags))
                ->setHard(true)
            ;
            $this->dispatchPurgeMessage($purgeMessage);
            $this->hardPurgeCacheTagContainer->reset();
        }

        $uniqueSoftPurgeTags = $this->softPurgeCacheTagContainer->getTags();
        if ($uniqueSoftPurgeTags) {
            $purgeMessage = new CacheProxyPurgeMessage();
            $purgeMessage
                ->setUrl($this->cacheHost)
                ->setXKey(implode(' ', $uniqueSoftPurgeTags))
                ->setHard(false)
            ;
            $this->dispatchPurgeMessage($purgeMessage);
            $this->softPurgeCacheTagContainer->reset();
        }
    }

    private function dispatchPurgeMessage(CacheProxyPurgeMessage $purgeMessage): void
    {
        if ($this->cacheProxyPurgeEnabled) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->messenger->dispatch($purgeMessage);
        }
        if ($this->debugEnabled) {
            $this->damLogger->info('Cache Proxy purge', 'purge xkeys', [
                'enabled' => $this->cacheProxyPurgeEnabled ? 'true' : 'false',
                'url' => $purgeMessage->getUrl(),
                'xkey' => $purgeMessage->getXKey(),
            ]);
        }
    }
}
