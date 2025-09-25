<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\CommonBundle\Messenger\Middleware\ContextIdentityMiddleware;
use App\Messenger\Message\AssetChangedMessage;
use App\Messenger\Message\AssetFileRouteMessage;
use App\Messenger\Message\CdnPurgeMessage;
use App\Messenger\Message\ImageCachePurgeMessage;
use App\Messenger\Message\MediaApiCallbackMessage;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $appName = 'core_dam';
    $cachePurge = 'anzu_core_dam_cache_purge';
    $assetChangedSync = 'anzu_core_dam_asset_changed_sync';
    $anzuCoreMediaApiCallback = 'anzu_core_dam_media_api_callback';
    $coreDamLog = 'core_dam_log';

    $messengerConfig = $config->messenger();
    $messengerConfig
        ->transport($cachePurge)
            ->dsn(env('MESSENGER_TRANSPORT_DSN'))
            ->options([
                'client_config' => [
                    'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
                ],
                'topic' => createBasicTopicConfig($cachePurge, $appName),
                'subscription' => createBasicSubscriptionConfig($cachePurge, $appName),
            ])
    ;
    $messengerConfig
        ->transport($assetChangedSync)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->options([
            'client_config' => [
                'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
            ],
            'topic' => createBasicTopicConfig($assetChangedSync, $appName),
            'subscription' => createBasicSubscriptionConfig($assetChangedSync, $appName),
        ])
    ;
    $messengerConfig
        ->transport($anzuCoreMediaApiCallback)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->options([
            'client_config' => [
                'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
            ],
            'topic' => createBasicTopicConfig($anzuCoreMediaApiCallback, $appName),
            'subscription' => createBasicSubscriptionConfig($anzuCoreMediaApiCallback, $appName),
        ])
    ;
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
    $messengerConfig
        ->routing(AssetFileRouteMessage::class)
        ->senders([$cachePurge])
    ;
    $messengerConfig
        ->routing(CdnPurgeMessage::class)
        ->senders([$cachePurge])
    ;
    $messengerConfig
        ->routing(ImageCachePurgeMessage::class)
        ->senders([$cachePurge])
    ;
    $messengerConfig
        ->routing(MediaApiCallbackMessage::class)
        ->senders([$anzuCoreMediaApiCallback])
    ;
    $messengerConfig
        ->routing(AssetChangedMessage::class)
        ->senders([$assetChangedSync])
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
