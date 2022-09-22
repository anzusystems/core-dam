<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Configuration;

final class CookieConfiguration
{
    public function __construct(
        private readonly string $domain,
        private readonly bool $secure,
        private readonly string $jwtPayloadCookieName,
        private readonly string $jwtSignatureCookieName,
        private readonly int $jwtLifetime,
        private readonly string $refreshTokenCookieName,
        private readonly int $refreshTokenLifetime,
    ) {
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function getJwtPayloadCookieName(): string
    {
        return $this->jwtPayloadCookieName;
    }

    public function getJwtSignatureCookieName(): string
    {
        return $this->jwtSignatureCookieName;
    }

    public function getJwtLifetime(): int
    {
        return $this->jwtLifetime;
    }

    public function getRefreshTokenCookieName(): string
    {
        return $this->refreshTokenCookieName;
    }

    public function getRefreshTokenLifetime(): int
    {
        return $this->refreshTokenLifetime;
    }
}
