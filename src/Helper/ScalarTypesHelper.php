<?php

declare(strict_types=1);

namespace App\Helper;

final class ScalarTypesHelper
{
    public static function getIntOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
