<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\User\BaseUserDto;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\CommonBundle\Traits\ValidatorAwareTrait;
use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Entity\User;
use App\Model\Domain\User\DamUserDto;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Notification\UserNotificationDispatcher;
use Throwable;

/**
 * Complete User processing.
 */
final class UserFacade
{
    use ValidatorAwareTrait;

    public function __construct(
        private readonly UserManager $manager,
        private readonly UserNotificationDispatcher $userNotificationDispatcher,
        private readonly OAuth2HttpClient $OAuth2HttpClient,
        private readonly DamLogger $damLogger
    ) {
    }

    /**
     * Process creation of User.
     *
     * @throws ValidationException
     * @throws SerializerException
     */
    public function createUser(UserDto $userDto): User
    {
        $this->setupIdFromSso($userDto);
        $this->validator->validate($userDto);

        $user = new User();
        $this->manager->createAnzuUser($user, $userDto);

        return $user;
    }

    /**
     * @throws ValidationException
     * @throws SerializerException
     */
    public function updateFromBaseUserDto(User $user, BaseUserDto $baseUserDto): User
    {
        $this->validator->validate($baseUserDto);
        $this->manager->updateBaseAnzuUser($user, $baseUserDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * @throws ValidationException
     * @throws SerializerException
     */
    public function updateFromDamUserDto(User $user, DamUserDto $userDto): User
    {
        $this->validator->validate($userDto);
        $this->manager->updateFromDamUserDto($user, $userDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    public function updateFromCurrentUserDto(User $user, UpdateCurrentUserDto $currentUserDto): User
    {
        $this->validator->validate($currentUserDto);
        $user = $this->manager->updateFromCurrentUserDto($user, $currentUserDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * @throws SerializerException
     */
    private function setupIdFromSso(UserDto $userDto): void
    {
        if (is_int($userDto->getId())) {
            return;
        }

        try {
            $ssoUserDto = $this->OAuth2HttpClient->getSsoUserInfoByEmail($userDto->getEmail());
            $userDto->setId((int) $ssoUserDto->getId());
        } catch (Throwable $e) {
            $this->damLogger->error('User', 'Can\'t get user ID by email ' . $userDto->getEmail(), exception: $e);

            return;
        }
    }
}
