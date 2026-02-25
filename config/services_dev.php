<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Dev\DevSsoUserClient;
use App\HttpClient\Sso\SsoUserClientInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(DevSsoUserClient::class);
    $services->alias(SsoUserClientInterface::class, DevSsoUserClient::class);
};
