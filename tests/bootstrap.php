<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if (filter_var($_ENV['CLEAR_CACHE'], FILTER_VALIDATE_BOOLEAN)) {
    passthru(sprintf(
        'php "%s/../bin/console" cache:clear --no-warmup  --env=test',
         __DIR__
    ));
}

if (filter_var($_ENV['DROP_DATABASE'], FILTER_VALIDATE_BOOLEAN)) {
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:database:drop --if-exists --force --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:database:create --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:migrations:sync-metadata-storage --no-interaction --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:schema:update --force --complete --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzu:mandatory-create:user --no-interaction --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzu-dam:ext-system:sync --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzusystems:fixtures:generate --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzu:elastic:rebuild asset --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzu:elastic:rebuild keyword --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzu:elastic:rebuild author --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/../bin/console" anzu-dam:distribution:sync-category-select --env=test',
        __DIR__
    ));
}
