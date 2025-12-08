<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Exception\InvalidCropException;
use AnzuSystems\CoreDamBundle\Exception\RemoteProcessingWaitingException;
use App\Exception\PubNotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Config\SentryConfig;

return static function (SentryConfig $config): void {
    $config
        ->dsn(env('SENTRY_DSN'))
        ->registerErrorListener(false)
        ->registerErrorHandler(false)
    ;
    $config
        ->options()
          ->enableTracing(true)
          ->tracesSampleRate(env('SENTRY_TRACES_SAMPLE_RATE')->float())
          ->profilesSampleRate(env('SENTRY_PROFILES_SAMPLE_RATE')->float())
          ->ignoreExceptions([
              AccessDeniedException::class,
              NotFoundHttpException::class,
              ResourceNotFoundException::class,
              ValidationException::class,
              PubNotFoundHttpException::class,
              InvalidCropException::class,
              RemoteProcessingWaitingException::class,
              MethodNotAllowedException::class,
          ])
    ;

    $config
        ->options()
            ->environment(env('APP_DEPLOY_ENV'))
            ->release(env('APP_VERSION'))
        ;
};
