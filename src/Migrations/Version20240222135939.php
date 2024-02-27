<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240222135939 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();

        $this->addSql(
            "INSERT INTO asset_licence_group (id, ext_system_id, created_by_id, modified_by_id, name, created_at, modified_at) VALUES 
                (1, 1, {$idConsole}, {$idConsole}, 'Sme family', NOW(), NOW()),
                (2, 1, {$idConsole}, {$idConsole}, 'Spectator', NOW(), NOW())
        "
        );
        $this->addSql('
                INSERT INTO asset_licence_in_group (asset_licence_id, asset_licence_group_id) VALUES
                (100000, 1),
                (100006, 1),
                (100001, 2),
                (100006, 2)
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_id = 100001 AND asset_licence_group_id = 2');
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_id = 100006 AND asset_licence_group_id = 2');
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_id = 100000 AND asset_licence_group_id = 1');
        $this->addSql('DELETE FROM core_dam.asset_licence_in_group WHERE asset_licence_id = 100006 AND asset_licence_group_id = 1');
        $this->addSql('DELETE FROM asset_licence_group where id IN (1,2)');
    }
}
