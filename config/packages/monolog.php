<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LogLevel;
use Symfony\Config\MonologConfig;

return static function (MonologConfig $config): void {
    $config
        ->handler('syslog')
            ->type('stream')
            ->path('php://stderr')
            ->level(LogLevel::NOTICE)
            ->channels()
                ->elements([
                    '!app',
                    '!audit',
                    '!app_sync',
                    '!audit_sync',
                ])
    ;
    $config
        ->handler('console')
            ->type('console')
            ->processPsr3Messages(false)
            ->channels()
                ->elements([
                    '!event',
                    '!doctrine',
                    '!console',
                    '!app',
                    '!audit',
                    '!app_sync',
                    '!audit_sync',
                ])
    ;
};
