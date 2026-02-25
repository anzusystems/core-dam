<?php

declare(strict_types=1);

namespace App\Dev;

use AnzuSystems\AuthBundle\Model\SsoUserDto;
use App\HttpClient\Sso\DefaultSsoUserClient;
use App\HttpClient\Sso\SsoUserClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class DevSsoUserClient implements SsoUserClientInterface
{
    public function __construct(
        private readonly DefaultSsoUserClient $defaultClient,
        #[Autowire(env: 'AUTH_OAUTH2_USER_INFO_URL')]
        private readonly string $userInfoUrl = '',
    ) {
    }

    public function getSsoUserInfo(string $id): SsoUserDto
    {
        if (empty($this->userInfoUrl)) {
            return (new SsoUserDto())
                ->setId($id)
                ->setEmail('e2etesting+central' . $id . '@petitpress.sk');
        }

        return $this->defaultClient->getSsoUserInfo($id);
    }
}
