<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CommonBundle\Helper\CollectionHelper;
use AnzuSystems\CoreDamBundle\Event\AssetChangedEvent;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\AssetChangedMessage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsEventListener(event: AssetChangedEvent::class)]
final class AssetChangedEventListener
{
    use MessageBusAwareTrait;

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(AssetChangedEvent $event): void
    {
        $this->messageBus->dispatch(new AssetChangedMessage(
            assetIds: CollectionHelper::traversableToIds($event->getAffectedAssets())
        ));
    }
}
