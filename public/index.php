<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return static function (array $context): Kernel {
    return new Kernel(
        appNamespace: $context['APP_NAMESPACE'],
        appSystem: $context['APP_SYSTEM'],
        appVersion: $context['APP_VERSION'],
        appReadOnlyMode: (bool) $context['APP_READ_ONLY_MODE'],
        environment: $context['APP_ENV'],
        debug: (bool) $context['APP_DEBUG'],
    );
};