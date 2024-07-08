<?php

namespace App\Model\Enum;

use AnzuSystems\Contracts\Model\Enum\BaseEnumTrait;
use AnzuSystems\Contracts\Model\Enum\EnumInterface;

enum ArtemisMediaType: string implements EnumInterface
{
    use BaseEnumTrait;

    case Video = 'video';
    case Audio = 'audio';

    public const ArtemisMediaType Default = self::Video;
}
