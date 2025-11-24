<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Model\User\BaseUserDto;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\CommonBundle\Validator\Validator;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Entity\User;
use App\Model\Domain\User\CreateUserDto;
use App\Model\Domain\User\DamUserDto;
use App\Model\Domain\User\UpdateCurrentUserDto;
use App\Model\Domain\User\UpdateUserDto;

/**
 * Complete User processing.
 */
final readonly class UserFacade
{
    public function __construct(
        private Validator $validator,
        private UserManager $manager,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function updateFromCurrentUserDto(User $user, UpdateCurrentUserDto $currentUserDto): User
    {
        $this->validator->validate($currentUserDto);

        return $this->manager->updateFromCurrentUserDto($user, $currentUserDto);
    }

    /**
     * @throws ValidationException
     */
    public function updateFromDamUserDto(User $user, DamUserDto $userDto): User
    {
        $this->validator->validate($userDto);
        $this->manager->updateFromDamUserDto($user, $userDto);

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function updateFromBaseUserDto(User $user, BaseUserDto $baseUserDto): User
    {
        $this->validator->validate($baseUserDto);
        $this->manager->updateBaseAnzuUser($user, $baseUserDto);

        return $user;
    }

    /**
     * Process creation of User.
     *
     * @throws ValidationException
     */
    public function createUser(UserDto $userDto): User
    {
        $this->validator->validate($userDto);

        $user = new User();
        $this->manager->createAnzuUser($user, $userDto);

        return $user;
    }
}
