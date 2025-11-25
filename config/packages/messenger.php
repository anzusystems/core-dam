<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\CommonBundle\Messenger\Middleware\ContextIdentityMiddleware;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $appName = 'core_dam';
    $coreDamLog = 'core_dam_log';

    $messengerConfig = $config->messenger();
    $messengerConfig
        ->transport($coreDamLog)
            ->dsn(env('MESSENGER_TRANSPORT_DSN'))
            ->options([
                'client_config' => [
                    'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
                ],
                'topic' => createBasicTopicConfig($coreDamLog, $appName),
                'subscription' => createBasicSubscriptionConfig($coreDamLog, $appName),
            ])
    ;

    $messengerConfig
        ->bus('messenger.bus.default')
            ->middleware(ContextIdentityMiddleware::class)
    ;
};

function createBasicTopicConfig(string $name, string $appName): array
{
    return [
        'name' => $name,
        'options' => [
            'labels' => [
                'application' => $appName,
                'name' => $name,
                'topic' => $name,
            ],
        ],
    ];
}

function createBasicSubscriptionConfig(string $name, string $appName): array
{
    return [
        'name' => $name,
        'options' => [
            'labels' => [
                'application' => $appName,
                'name' => $name,
            ],
            'retryPolicy' => [
                'minimumBackoff' => '2s',
                'maximumBackoff' => '600s',
            ],
        ],
    ];
}
