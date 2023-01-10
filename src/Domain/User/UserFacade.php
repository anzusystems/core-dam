<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Model\Domain\User\UpdateUserDto;
use App\Notification\UserNotificationDispatcher;

/**
 * Complete User processing.
 */
final class UserFacade
{
    public function __construct(
        private readonly EntityValidator $validator,
        private readonly UserManager $userManager,
        private readonly UserNotificationDispatcher $userNotificationDispatcher,
    ) {
    }

    /**
     * Process creating of user from DTO.
     *
     * @throws ValidationException
     */
    public function createFromDto(CreateUserDto $createUserDto): User
    {
        $this->validator->validateDto($createUserDto);

        return $this->userManager->createFromDto($createUserDto);
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
        $user = $this->userManager->updateFromDto($user, $updateUserDto);
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
        $user = $this->userManager->updateFromCurrentUserDto($user, $currentUserDto);
        $this->userNotificationDispatcher->notifyUserUpdated((int) $user->getId());

        return $user;
    }

    /**
     * Process deletion of user.
     */
    public function delete(User $user): void
    {
        $this->userManager->delete($user);
    }
}
