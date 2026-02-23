<?php

declare(strict_types=1);

namespace App\HttpClient\Sso;

use AnzuSystems\AuthBundle\Exception\UnsuccessfulAccessTokenRequestException;
use AnzuSystems\AuthBundle\Exception\UnsuccessfulUserInfoRequestException;
use AnzuSystems\AuthBundle\HttpClient\OAuth2HttpClient;
use AnzuSystems\AuthBundle\Model\SsoUserDto;

final class DefaultSsoUserClient implements SsoUserClientInterface
{
    public function __construct(
        private readonly OAuth2HttpClient $OAuth2HttpClient,
    ) {
    }

    /**
     * @throws UnsuccessfulAccessTokenRequestException
     * @throws UnsuccessfulUserInfoRequestException
     */
    public function getSsoUserInfo(string $id): SsoUserDto
    {
        return $this->OAuth2HttpClient->getSsoUserInfo($id);
    }
}
