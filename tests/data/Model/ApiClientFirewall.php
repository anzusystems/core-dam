<?php

declare(strict_types=1);

namespace App\Tests\data\Model;

use AnzuSystems\Contracts\Model\Enum\BaseEnumTrait;
use AnzuSystems\Contracts\Model\Enum\EnumInterface;
use LogicException;

enum ApiClientFirewall: string implements EnumInterface
{
    use BaseEnumTrait;

    public const ID_BLOG_SYS_API = 1_963_060;

    case Admin = 'adm';
    case Ugc = 'ugc';
    case Sys = 'sys';
    case Pub = 'pub';

    public function getSysToken(int $userId): string
    {
        return match ($userId) {
            self::ID_BLOG_SYS_API => self::ID_BLOG_SYS_API . ':TOKEN_sys_anzu_blog',
            default => new LogicException(sprintf('Undefined sys token mapping for user id "%d"', $userId))
        };
    }
}
