<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Configuration;

use AnzuSystems\AuthBundle\Model\Enum\JwtAlgorithm;

final class JwtConfiguration
{
    public function __construct(
        private readonly string $audience,
        private readonly string $algorithm,
        private readonly string $publicCert,
        private readonly string $privateCert,
    ) {
    }

    public function getAudience(): string
    {
        return $this->audience;
    }

    public function getAlgorithm(): JwtAlgorithm
    {
        return JwtAlgorithm::from($this->algorithm);
    }

    public function getPublicCert(): string
    {
        return $this->publicCert;
    }

    public function getPrivateCert(): string
    {
        return $this->privateCert;
    }
}
