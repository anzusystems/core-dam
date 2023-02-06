<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

use AnzuSystems\CommonBundle\Controller\PermissionController;
use Symfony\Component\HttpFoundation\Request;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('anzu_systems_common.permissions.config', '/api/adm/v1/permissions/config')
            ->methods([Request::METHOD_GET])
            ->controller([PermissionController::class, 'getConfig'])
    ;
};
