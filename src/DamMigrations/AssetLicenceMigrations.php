<?php

declare(strict_types=1);

namespace App\DamMigrations;

use App\App;
use App\Entity\User;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;

final class AssetLicenceMigrations extends AbstractMigrations
{
    /**
     * @throws Exception
     */
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $this->prepareBulkInsert(
            'asset_licence',
            [
                'id' => 100000,
                'ext_system_id' => 1,
                'ext_id' => '',
                'name' => 'CMS licence',
                'limited_files' => 0,
                'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
            ]
        );

        $this->flush();
    }
}
