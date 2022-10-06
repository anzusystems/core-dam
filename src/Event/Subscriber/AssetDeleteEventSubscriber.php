<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Domain\Image\Crop\CropCache;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Event\AssetDeleteEvent;
use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Notification\AssetNotificationDispatcher;
use League\Flysystem\FilesystemException;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AssetDeleteEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AssetNotificationDispatcher $notificationDispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AssetDeleteEvent::class => 'deleteAssetFile',
        ];
    }

    /**
     * @throws SerializerException
     */
    public function deleteAssetFile(AssetDeleteEvent $event): void
    {
        $this->notificationDispatcher->notifyAssetDeleted($event);
    }
}
