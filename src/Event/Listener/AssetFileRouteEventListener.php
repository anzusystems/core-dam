<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetSlot;
use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileRouteEvent;
use AnzuSystems\CoreDamBundle\Event\Dispatcher\AssetChangedEventDispatcher;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Cache\AssetFileCachePurger;
use App\Cache\CacheCdnPurger;
use App\Configuration\ConfigurationProvider;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsEventListener(event: AssetFileRouteEvent::class)]
final class AssetFileRouteEventListener
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly ConfigurationProvider $configurationProvider,
        private readonly AssetChangedEventDispatcher $assetMetadataBulkEventDispatcher,
        private readonly AudioFileRepository $audioFileRepository,
        private readonly AssetFileCachePurger $assetFileCachePurger,
        private readonly CacheCdnPurger $cacheCdnPurger,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(AssetFileRouteEvent $event): void
    {
        $this->assetFileCachePurger->purge($event->getAssetType(), $event->getAssetFileId());
        $this->cacheCdnPurger->addUrl($event->getFullUrl());

        $this->dispatchAssetChangedEvent($event);
    }

    private function dispatchAssetChangedEvent(AssetFileRouteEvent $event): void
    {
        $audioFile = $this->audioFileRepository->find($event->getAssetFileId());
        if (null === $audioFile) {
            return;
        }

        if (false === ($audioFile instanceof AudioFile)) {
            return;
        }

        $freeAssets = $audioFile->getSlots()
            ->filter(
                fn (AssetSlot $slot): bool => $slot->getName() === $this->configurationProvider->getAudioDistribution()->getAudioFreeSlotName()
            )->map(
                static fn (AssetSlot $slot): Asset => $slot->getAsset()
            )
        ;

        // dispatch to CORE CMS
        $this->assetMetadataBulkEventDispatcher->dispatchAssetChangedEvent($freeAssets);
    }
}
