<?php

declare(strict_types=1);

namespace App\Model\Enum;

use AnzuSystems\Contracts\Model\Enum\BaseEnumTrait;
use AnzuSystems\Contracts\Model\Enum\EnumInterface;

/**
 * @link https://dev.azure.com/petitpress/Anzu/_git/gate?anchor=response-codes-for-%60access_content%60-and-%60access_no_advert%60-variables
 *
 * Status values (in link above) are provided by a Gate on HAProxy.
 */
enum GateStatus: string implements EnumInterface
{
    use BaseEnumTrait;

    case Unknown = '0';
    case Error = '1001';
    case Unlocked = '1';
    case LockedUserNotLoggedOrNotPaid = '2'; // Do not unlock, user is not logged in or is logged in but has no roles to unlock the content.
    case LockedUserLoggedDeviceLimitReached = '3'; // Do not unlock, user is logged in and has roles to unlock content, but the device limit has reached.
    case LockedWithSubscriptionButNotRequiredRole = '5'; // Do not unlock, user is logged in and has roles to unlock content, but doesn't have a required role.

    public const self Default = self::Unlocked;

    public function isContentUnlocked(): bool
    {
        return $this->in([self::Unlocked, self::Error, self::Unknown]);
    }

    public function isContentLocked(): bool
    {
        return false === $this->isContentUnlocked();
    }

    public function isNoAdvertKept(): bool
    {
        return $this->in([self::Unlocked]);
    }

    public function isAdvertKept(): bool
    {
        return false === $this->isNoAdvertKept();
    }
}
