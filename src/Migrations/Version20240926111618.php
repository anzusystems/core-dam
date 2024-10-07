<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240926111618 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100013, 1, '{$idConsole}', '{$idConsole}', 'Novyny', null, 0, NOW(), NOW())
        "
        );
        $this->addSql(
            "INSERT INTO asset_licence_group (id, ext_system_id, created_by_id, modified_by_id, name, created_at, modified_at) VALUES 
                (3, 1, {$idConsole}, {$idConsole}, 'Novyny', NOW(), NOW())
            "
        );
        $this->addSql('
                INSERT INTO asset_licence_in_group (asset_licence_id, asset_licence_group_id) VALUES
                (100013, 1),
                (100001, 1),
                (100002, 1),
                (100013, 2),
                (100000, 2),
                (100002, 2),
                (100001, 3),
                (100006, 3),
                (100000, 3),
                (100013, 3),
                (100002, 3)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_group_id = 1');
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_group_id = 2');
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_group_id = 3');
        $this->addSql('DELETE FROM asset_licence_group where id = 3');
        $this->addSql('DELETE FROM asset_licence where id = 100013');
    }
}
