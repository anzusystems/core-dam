<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Event\UserDeletePersonalDataEvent;
use App\Domain\User\UserManager;
use App\Entity\User;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UserDeletePersonalDataEvent::class)]
final readonly class UserDeletePersonalDataEventListener
{
    public function __construct(
        private UserManager $userManager,
    ) {
    }

    public function __invoke(UserDeletePersonalDataEvent $event): void
    {
        /** @var User $user */
        $user = $event->getUser();
        $this->userManager->deletePersonalData($user);
    }
}
