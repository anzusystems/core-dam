<?php

declare(strict_types=1);

namespace App\Security\Permission;

final class Grants
{
    public const GRANT_DENY = 0;
    public const GRANT_ALLOW_OWNER = 1;
    public const GRANT_ALLOW = 2;
}
