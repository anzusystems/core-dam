<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\CommonBundle\Messenger\Middleware\ContextIdentityMiddleware;
use AnzuSystems\CoreDamBundle\Messenger\Message\JobAudioNarrationMessage;
use App\Messenger\Message\AssetChangedMessage;
use App\Messenger\Message\CacheCdnPurgeMessage;
use App\Messenger\Message\CacheProxyPurgeMessage;
use App\Messenger\Message\MediaApiCallbackMessage;
use App\Messenger\Serializer\AnzuMessengerSerializer;
use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $appName = 'core_dam';
    $assetChangedSync = 'anzu_core_dam_asset_changed_sync';
    $anzuCoreMediaApiCallback = 'anzu_core_dam_media_api_callback';
    $coreDamLog = 'core_dam_log';
    $cachePurge = 'purger_proxy_purge';
    $cacheCdnPurge = 'purger_cdn_proxy_purge';
    // TTS transport — routes JobAudioNarrationMessage to a dedicated Pub/Sub topic.
    // TODO T9.5: Create the anzu_core_dam_tts Pub/Sub topic + subscription in infra before enabling in prod.
    $damTts = 'anzu_core_dam_tts';

    $messengerConfig = $config->messenger();
    $messengerConfig
        ->transport($assetChangedSync)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->option('client_config', [
            'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
        ])
        ->option('topic', createBasicTopicConfig($assetChangedSync, $appName))
        ->option('subscription', createBasicSubscriptionConfig($assetChangedSync, $appName))
    ;
    $messengerConfig
        ->transport($anzuCoreMediaApiCallback)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->option('client_config', [
            'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
        ])
        ->option('topic', createBasicTopicConfig($anzuCoreMediaApiCallback, $appName))
        ->option('subscription', createBasicSubscriptionConfig($anzuCoreMediaApiCallback, $appName))
    ;
    $messengerConfig
        ->transport($coreDamLog)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->option('client_config', [
            'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
        ])
        ->option('topic', createBasicTopicConfig($coreDamLog, $appName))
        ->option('subscription', createBasicSubscriptionConfig($coreDamLog, $appName))
    ;
    $messengerConfig
        ->transport($cachePurge)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->option('client_config', [
            'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
        ])
        ->option('topic', createBasicTopicConfig($cachePurge, $appName))
        ->serializer(AnzuMessengerSerializer::class)
    ;
    $messengerConfig
        ->transport($cacheCdnPurge)
        ->dsn(env('MESSENGER_TRANSPORT_DSN'))
        ->option('client_config', [
            'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
        ])
        ->option('topic', createBasicTopicConfig($cacheCdnPurge, $appName))
        ->serializer(AnzuMessengerSerializer::class)
    ;
    // TTS transport: routes JobAudioNarrationMessage to a dedicated Pub/Sub topic.
    // MESSENGER_TRANSPORT_DSN_DAM_TTS must be set (in dev: same value as MESSENGER_TRANSPORT_DSN;
    // in prod: a dedicated DSN once T9.5 provisions the anzu_core_dam_tts topic/subscription).
    $messengerConfig
        ->transport($damTts)
        ->dsn(env('MESSENGER_TRANSPORT_DSN_DAM_TTS'))
        ->option('client_config', [
            'credentials' => '%env(json:base64:GOOGLE_PUBSUB_SA_KEY)%',
        ])
        ->option('topic', createBasicTopicConfig($damTts, $appName))
        ->option('subscription', createBasicSubscriptionConfig($damTts, $appName))
    ;

    $messengerConfig
        ->bus('messenger.bus.default')
        ->middleware(ContextIdentityMiddleware::class)
    ;
    $messengerConfig
        ->routing(MediaApiCallbackMessage::class)
        ->senders([$anzuCoreMediaApiCallback])
    ;
    $messengerConfig
        ->routing(AssetChangedMessage::class)
        ->senders([$assetChangedSync])
    ;
    $messengerConfig
        ->routing(CacheProxyPurgeMessage::class)
        ->senders([$cachePurge])
    ;
    $messengerConfig
        ->routing(CacheCdnPurgeMessage::class)
        ->senders([$cacheCdnPurge])
    ;
    $messengerConfig
        ->routing(JobAudioNarrationMessage::class)
        ->senders([$damTts])
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
