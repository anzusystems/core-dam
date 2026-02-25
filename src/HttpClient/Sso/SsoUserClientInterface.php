<?php

declare(strict_types=1);

namespace App\HttpClient\Sso;

use AnzuSystems\AuthBundle\Model\SsoUserDto;

interface SsoUserClientInterface
{
    public function getSsoUserInfo(string $id): SsoUserDto;
}
