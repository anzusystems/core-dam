<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\UserTrackingEvent;
use App\Security\Authenticator\Token\UgcImpAuthenticationToken;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserTrackingEvent::class)]
final readonly class UserTrackingListener
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function __invoke(UserTrackingEvent $event): void
    {
        $token = $this->security->getToken();
        if (false === ($token instanceof UgcImpAuthenticationToken)) {
            return;
        }

        $event->setUser(
            $token->getOriginalUser()
        );
    }
}
