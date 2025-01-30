<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use AnzuSystems\CommonBundle\Exception\Handler\ExceptionHandlerInterface;
use AnzuSystems\Contracts\AnzuApp;
use App\Controller\Traits\PubCacheTrait;
use App\Exception\PubNotFoundHttpException;
use App\Model\Request\CacheSettings;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

final class PubNotFoundHttpExceptionHandler implements ExceptionHandlerInterface
{
    use PubCacheTrait;

    public const string ERROR = 'not_found';

    public function __construct(
        #[Autowire(param: 'app_cache_proxy_enabled')]
        private readonly bool $appCacheProxyEnabled,
    ) {
    }

    public function getErrorResponse(Throwable $exception): JsonResponse
    {
        $response = new JsonResponse(
            [
                'error' => self::ERROR,
                'detail' => $exception->getMessage(),
                'contextId' => AnzuApp::getContextId(),
            ],
            JsonResponse::HTTP_NOT_FOUND
        );

        if ($this->appCacheProxyEnabled) {
            $this->setCache(
                response: $response,
                cacheSettings: new CacheSettings()
            );
        }

        return $response;
    }

    public function getSupportedExceptionClasses(): array
    {
        return [PubNotFoundHttpException::class];
    }
}
