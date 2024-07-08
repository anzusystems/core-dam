<?php

declare(strict_types=1);

namespace App\Domain\User;

use AnzuSystems\AuthBundle\Model\SsoUserDto;
use AnzuSystems\CommonBundle\Domain\User\AbstractUserManager;
use AnzuSystems\CommonBundle\Model\User\UserDto;
use AnzuSystems\Contracts\Entity\AnzuUser;
use App\Entity\User;

/**
 * User persistence management.
 */
final class UgcUserManager extends AbstractUserManager
{
    public function createFromSsoUserInfo(SsoUserDto $ssoUserDto, array $roles = [User::ROLE_UGC], bool $flush = false): AnzuUser
    {
        $userDto = (new UserDto())
            ->setId((int) $ssoUserDto->getId())
            ->setEmail($ssoUserDto->getEmail())
            ->setRoles($roles)
        ;

        return $this->createAnzuUser(new User(), $userDto, $flush);
    }
}
