<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\DoctrineMigrationsConfig;

return static function (DoctrineMigrationsConfig $config): void {
    $config
        ->storage()
            ->tableStorage()
                ->tableName('_doctrine_migration_versions')

    ;
    $config
        ->migrationsPath('App\Migrations', param('kernel.project_dir') . '/src/Migrations')
    ;
};
