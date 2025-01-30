<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use App\Model\Request\CacheSettings;
use Symfony\Component\HttpFoundation\Response;

trait PubCacheTrait
{
    public const string PRIVATE_CACHE_TTL_HEADER = 'X-Cache-Control-TTL';
    public const string REMOVE_COOKIE_HEADER = 'X-Remove-Cookie';
    public const string PRIVATE_CACHE_XKEY_HEADER = 'xkey';

    /**
     * Sets correct headers for proxy cache.
     */
    protected function setCache(Response $response, CacheSettings $cacheSettings): void
    {
        $response->setPublic();
        $response->setMaxAge($cacheSettings->getPublicTtl());
        $response->headers->set(self::PRIVATE_CACHE_TTL_HEADER, (string) $cacheSettings->getPrivateTtl());
        $response->headers->set(self::REMOVE_COOKIE_HEADER, '1');
        $response->headers->remove('Expires');

        $tags = $cacheSettings->getTagsString();
        if ($tags) {
            $response->headers->set(self::PRIVATE_CACHE_XKEY_HEADER, $tags);
        }
    }
}
