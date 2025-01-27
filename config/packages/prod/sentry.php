<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

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
    ;

    $config
        ->options()
            ->environment(env('APP_DEPLOY_ENV'))
            ->release(env('APP_VERSION'))
        ;
};
