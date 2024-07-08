<?php

declare(strict_types=1);

namespace App\HttpClient;

use AnzuSystems\CommonBundle\Traits\LoggerAwareRequest;
use AnzuSystems\CommonBundle\Traits\SerializerAwareTrait;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use JsonException;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CloudFlareClient implements LoggerAwareInterface
{
    use LoggerAwareRequest;
    use SerializerAwareTrait;

    private const string URL_TEMPLATE = '/client/v4/zones/%s/purge_cache';

    public function __construct(
        private readonly HttpClientInterface $cloudFlareApiClient,
        private readonly string $zoneId,
    ) {
    }

    /**
     * @throws JsonException
     */
    public function purgeCdn(array $paths): void
    {
        $response = $this->loggedRequest(
            client: $this->cloudFlareApiClient,
            message: '[Cloudflare] Purge',
            url: sprintf(self::URL_TEMPLATE, $this->zoneId),
            method: Request::METHOD_POST,
            json: [
                'files' => $paths,
            ],
            timeout: 15,
        );

        if ($response->hasError()) {
            throw new RuntimeException(sprintf(
                '[CloudFlare] Purge Purge CDN fails with status code: %s',
                $response->getStatusCode(),
            ));
        }
    }
}
