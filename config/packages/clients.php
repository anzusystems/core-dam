<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\FrameworkConfig;

return static function (FrameworkConfig $config): void {
    $httpClient = $config->httpClient();
    $httpClient->maxHostConnections(10)
        ->defaultOptions()
            ->maxRedirects(5)
            ->maxDuration(30)
            ->timeout(30)
    ;
    $httpClient->scopedClient('artemis.api_client')
        ->baseUri(env('DISTRIBUTION_ARTEMIS_CMS_ENDPOINT'))
        ->header('token', env('DISTRIBUTION_ARTEMIS_CMS_API_TOKEN'))
    ;
    $httpClient->scopedClient('mediaapi.api_client')
        ->baseUri(env('MEDIA_API_HOST'))
        ->authBearer(env('MEDIA_API_TOKEN')->string())
        ->header('Accept', 'application/json')
        ->header('Content-type', 'application/json')
    ;
    $httpClient->scopedClient('anzu_notification.api_client')
        ->baseUri(env('ANZU_NOTIFICATION_DOMAIN'))
        ->authBearer(env('ANZU_DAM_NOTIFICATION_API_TOKEN')->string())
        ->header('Accept', 'application/json')
        ->header('Content-type', 'application/json')
    ;
    $httpClient->scopedClient('anzu_cms.api_client')
        ->baseUri(env('ANZU_CMS_API_HOST'))
        ->authBearer(env('ANZU_CMS_API_TOKEN')->string())
        ->header('Accept', 'application/json')
        ->header('Content-type', 'application/json')
    ;

    $httpClient->scopedClient('cloudFlare.api_client')
        ->baseUri(env('CLOUD_FLARE_API_HOST'))
        ->authBearer(env('CLOUD_FLARE_API_TOKEN')->string())
        ->header('Accept', 'application/json')
        ->header('Content-type', 'application/json')
    ;
};
