<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Security\Util\JwtUgcUtil;
use App\Tests\ApiClient;
use App\Tests\HttpClient\BaseClient;
use App\Tests\HttpClient\CmsClientMock;
use App\Tests\HttpClient\RssPodcastMock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->set(RssPodcastMock::class);
    $services->set(BaseClient::class);
    $services->set(CmsClientMock::class);

    $services->set(HttpClientInterface::class . ' $httpClient', MockHttpClient::class)
        ->factory(service(RssPodcastMock::class));
    $services->set(HttpClientInterface::class . ' $client', MockHttpClient::class)
        ->factory(service(BaseClient::class));
    $services->set(HttpClientInterface::class . ' $anzuCmsApiClient', MockHttpClient::class)
        ->factory(service(CmsClientMock::class));

    $services->set(ApiClient::class);
    $services->get(JwtUgcUtil::class)->public();
};
