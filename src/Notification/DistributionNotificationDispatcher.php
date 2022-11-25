<?php

declare(strict_types=1);


namespace App\Notification;

use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Event\AssetFileDeleteEvent;
use AnzuSystems\CoreDamBundle\Event\DistributionAuthorized;
use AnzuSystems\CoreDamBundle\Event\DistributionStatusEvent;
use AnzuSystems\CoreDamBundle\Event\MetadataProcessedEvent;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Model\Domain\AssetFile\AsseFileAdmNotificationDecorator;
use App\Model\Domain\AssetFile\AssetFileStatusAdmNotificationDecorator;
use App\Model\Domain\Distribution\DistributionAdmNotificationDecorator;
use App\Model\Domain\Distribution\DistributionAuthorizedAdmNotificationDecorator;

final class DistributionNotificationDispatcher extends AbstractNotificationDispatcher
{
    private const EVENT_NAME_PREFIX = 'distribution_';
    private const EVENT_DISTRIBUTION_AUTHORIZED = 'distribution_authorized';

    use SerializerAwareTrait;

    /**
     * @throws SerializerException
     */
    public function notifyStatusChange(DistributionStatusEvent $event): void
    {
        $this->notify(
            [$event->getDistribution()->getNotifyTo()->getId()],
            self::EVENT_NAME_PREFIX . $event->getDistribution()->getStatus()->toString(),
            DistributionAdmNotificationDecorator::getInstance($event->getDistribution())
        );
    }

    /**
     * @throws SerializerException
     */
    public function notifyDistributionAuthorized(DistributionAuthorized $event): void
    {
        $this->notify(
            [$event->getTargetUserId()],
            self::EVENT_DISTRIBUTION_AUTHORIZED,
            DistributionAuthorizedAdmNotificationDecorator::getInstance($event)
        );
    }
}
