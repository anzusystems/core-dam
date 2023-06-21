<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Validator\Validator;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\UpdateUserDto;

/**
 * Complete User processing.
 */
final readonly class UserFacade
{
    public function __construct(
        private Validator $validator,
        private UserManager $userManager,
    ) {
    }

    /**
     * Process creating of user from DTO.
     *
     * @throws ValidationException
     */
    public function createFromDto(CreateUserDto $createUserDto): User
    {
        $this->validator->validate($createUserDto);

        return $this->userManager->createFromDto($createUserDto);
    }

    /**
     * Process updating of user from DTO.
     *
     * @throws ValidationException
     */
    public function updateFromDto(User $user, UpdateUserDto $updateUserDto): User
    {
        $this->validator->validate($updateUserDto);
        $this->userManager->updateFromDto($user, $updateUserDto);

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
