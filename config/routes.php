<?php

declare(strict_types=1);

namespace Symfony\Component\Routing\Loader\Configurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('routes/*.php');
    $routes->import('routes/{' . $routes->env() . '}/*.php');
};
