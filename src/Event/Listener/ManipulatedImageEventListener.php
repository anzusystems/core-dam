<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\ManipulatedImageEvent;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Cache\AssetFileCachePurger;
use App\Cache\ImageFileUrlCdnPurger;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ManipulatedImageEvent::class)]
final class ManipulatedImageEventListener
{
    use MessageBusAwareTrait;

    public function __construct(
        private readonly AssetFileCachePurger $assetFileCachePurger,
        private readonly ImageFileUrlCdnPurger $imageFileUrlCdnPurger,
    ) {

    }

    public function __invoke(ManipulatedImageEvent $event): void
    {
        // purge by xkey
        $this->assetFileCachePurger->purge(AssetType::Image, $event->getImageId());
        // purget image urls
        $this->imageFileUrlCdnPurger->purge(
            $event->getImageId(),
            $event->getRoiPositions(),
            $event->getExtSystem()
        );
    }
}
