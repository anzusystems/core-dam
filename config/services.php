<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $configurator): void {
    $configurator
        ->parameters()
            // Preloading
            ->set('container.dumper.inline_factories', true)
            ->set('container.dumper.inline_class_loader', true)
    ;

    $configurator->import('services/');

    if ('dev' === $configurator->env()) {
        $configurator->import('services_dev.php');
    }

    if ('test' === $configurator->env()) {
        $configurator->import('services_test.php');
    }
};
