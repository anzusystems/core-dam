<?php

declare(strict_types=1);


namespace App\Event\Subscriber;

use AnzuSystems\CoreDamBundle\Event\DistributionAuthorized;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Notification\DistributionNotificationDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DistributionAuthorizedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly DistributionNotificationDispatcher $dispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DistributionAuthorized::class => 'onDistributionAuthorized'
        ];
    }

    /**
     * @throws SerializerException
     */
    public function onDistributionAuthorized(DistributionAuthorized $event): void
    {
        $this->dispatcher->notifyDistributionAuthorized($event);
    }
}
