<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\Contracts\Model\User\UserDto;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Entity\User;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Model\Domain\User\UpdateUserDto;
use App\Notification\UserNotificationDispatcher;

/**
 * Complete User processing.
 */
final readonly class UserFacade
{
    public function __construct(
        private EntityValidator $validator,
        private UserManager $manager,
        private UserNotificationDispatcher $userNotificationDispatcher,
    ) {
    }

    /**
     * Process updating of users permissions/roles.
     *
     * @throws ValidationException
     * @throws SerializerException
     */
    public function updateAnzuUser(User $user, UserDto $userDto): User
    {
        $this->validator->validateDto($userDto);
        $this->manager->updateAnzuUser($user, $userDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * Process creation of User.
     *
     * @throws ValidationException
     */
    public function createAnzuUser(UserDto $userDto): User
    {
        $this->validator->validateDto($userDto);

        $user = new User();
        $this->manager->createAnzuUser($user, $userDto);

        return $user;
    }

    /**
     * Process updating of user from DTO.
     *
     * @throws ValidationException
     * @throws SerializerException
     */
    public function updateFromDto(User $user, UpdateUserDto $updateUserDto): User
    {
        $this->validator->validateDto($updateUserDto);
        $user = $this->manager->updateFromUserDto($user, $updateUserDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    public function updateFromCurrentUserDto(User $user, UpdateCurrentUserDto $currentUserDto): User
    {
        $this->validator->validateDto($currentUserDto);
        $user = $this->manager->updateFromCurrentUserDto($user, $currentUserDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * Process deletion of user.
     */
    public function delete(User $user): void
    {
        $this->manager->delete($user);
    }
}
