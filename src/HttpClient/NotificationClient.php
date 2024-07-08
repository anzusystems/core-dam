<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Model\HttpClient\HttpClientResponse;
use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class NotificationClient implements LoggerAwareInterface
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    private const string CACHE_PATH = '/api/v1/sys/cache-proxy/purge';
    private const string CDN_PATH = '/api/v1/sys/cdn/purge';

    public function __construct(
        private readonly HttpClientInterface $anzuNotificationApiClient,
        private readonly string $cachePurgeUrl,
    ) {
    }

    public function purgeCacheProxy(string $xKey): HttpClientResponse
    {
        return $this->loggedRequest(
            client: $this->anzuNotificationApiClient,
            message: '[Anzu Notification] Purge cache proxy.',
            url: self::CACHE_PATH,
            method: Request::METHOD_POST,
            json: [
                'url' => $this->cachePurgeUrl,
                'xkey' => $xKey,
            ],
            timeout: 15,
        );
    }

    public function purgeCdn(array $paths): HttpClientResponse
    {
        return $this->loggedRequest(
            client: $this->anzuNotificationApiClient,
            message: '[Anzu Notification] Purge cdn.',
            url: self::CDN_PATH,
            method: Request::METHOD_POST,
            json: [
                'paths' => $paths,
            ],
            timeout: 15,
        );
    }
}
