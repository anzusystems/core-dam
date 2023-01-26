<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Security\Util\JwtUgcUtil;
use App\Tests\ApiClient;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(ApiClient::class);
    $services->get(JwtUgcUtil::class)->public();
};
