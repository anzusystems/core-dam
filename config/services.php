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
};
