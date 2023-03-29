<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use Symfony\Component\HttpFoundation\Request;
use  AnzuSystems\CommonBundle\Controller\HealthCheckController;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('anzu_systems_common.health_ceck', '/api/sys/v1/health')
            ->methods([Request::METHOD_GET])
            ->controller([HealthCheckController::class, 'healthCheck'])
    ;
};
