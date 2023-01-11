<?php

declare(strict_types=1);

namespace App\Model;

final readonly class UgcCookieConfiguration
{
    public function __construct(
        private string $jwtPayloadPartName,
        private string $jwtSignaturePartName,
        private string $jwtImpName,
    ) {
    }

    public function getJwtPayloadPartName(): string
    {
        return $this->jwtPayloadPartName;
    }

    public function getJwtSignaturePartName(): string
    {
        return $this->jwtSignaturePartName;
    }

    public function getJwtImpName(): string
    {
        return $this->jwtImpName;
    }
}
