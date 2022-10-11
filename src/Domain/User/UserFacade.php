<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateUserDto;

/**
 * Complete User processing.
 */
final class UserFacade
{
    public function __construct(
        private readonly EntityValidator $validator,
        private readonly UserManager $userManager,
    ) {
    }

    /**
     * Process creating of user.
     *
     * @throws ValidationException
     */
    public function create(User $user): User
    {
        $this->validator->validate($user);
        $this->userManager->create($user);

        return $user;
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
     */
    public function updateFromDto(User $user, UpdateUserDto $updateUserDto): User
    {
        $this->validator->validateDto($updateUserDto);

        return $this->userManager->updateFromDto($user, $updateUserDto);
    }

    /**
     * Process updating of user.
     *
     * @throws ValidationException
     */
    public function update(User $user, User $newUser): User
    {
        $this->validator->validate($newUser, $user);
        $this->userManager->update($user, $newUser);

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
