<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LogLevel;
use Sentry\State\HubInterface;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $config): void {
    $config
        ->handler('sentry')
        ->type('sentry')
        ->hubId(HubInterface::class)
        ->level(LogLevel::WARNING)
    ;
};
