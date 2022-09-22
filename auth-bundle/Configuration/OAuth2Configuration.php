<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Configuration;

final class OAuth2Configuration
{
    /**
     * @param string[] $ssoScopes
     */
    public function __construct(
        private readonly string $ssoAccessTokenUrl,
        private readonly string $ssoAuthorizeUrl,
        private readonly string $ssoRedirectUrl,
        private readonly string $ssoUserInfoUrl,
        private readonly string $ssoRevokeTokenUrl,
        private readonly string $ssoClientId,
        private readonly string $ssoClientSecret,
        private readonly array $ssoScopes,
        private readonly string $ssoPublicCertPath,
        private readonly string $ssoPrivateCertPath,
        private readonly string $ssoPrivateCertPassphrase
    ) {
    }

    public function getSsoAccessTokenUrl(): string
    {
        return $this->ssoAccessTokenUrl;
    }

    public function getSsoAuthorizeUrl(): string
    {
        return $this->ssoAuthorizeUrl;
    }

    public function getSsoRedirectUrl(): string
    {
        return $this->ssoRedirectUrl;
    }

    public function getSsoUserInfoUrl(): string
    {
        return $this->ssoUserInfoUrl;
    }

    public function getSsoRevokeTokenUrl(): string
    {
        return $this->ssoRevokeTokenUrl;
    }

    public function getSsoClientId(): string
    {
        return $this->ssoClientId;
    }

    public function getSsoClientSecret(): string
    {
        return $this->ssoClientSecret;
    }

    public function getSsoScopes(): array
    {
        return $this->ssoScopes;
    }

    public function getSsoPublicCertPath(): string
    {
        return $this->ssoPublicCertPath;
    }

    public function getSsoPrivateCertPath(): string
    {
        return $this->ssoPrivateCertPath;
    }

    public function getSsoPrivateCertPassphrase(): string
    {
        return $this->ssoPrivateCertPassphrase;
    }
}
