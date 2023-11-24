<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231123110347 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();

        $this->addSql(
            "INSERT INTO `ext_system` (id, `name`, `slug`, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (10, 'Tlačové správy', 'pressNews', '{$idConsole}', '{$idConsole}', NOW(), NOW()),
            (11, 'Scraper', 'scraper', '{$idConsole}', '{$idConsole}', NOW(), NOW())
        ;
       "
        );

        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100001, 1, '{$idConsole}', '{$idConsole}', 'Spectator', null, 0, NOW(), NOW()),
            (100002, 1, '{$idConsole}', '{$idConsole}', 'xblock', null, 0, NOW(), NOW()),
            (100003, 1, '{$idConsole}', '{$idConsole}', 'Magazin RIP', null, 0, NOW(), NOW()),
            (100004, 1, '{$idConsole}', '{$idConsole}', 'Komerčné', null, 0, NOW(), NOW()),
            (100005, 1, '{$idConsole}', '{$idConsole}', 'Smeti', null, 0, NOW(), NOW()),
            (100006, 1, '{$idConsole}', '{$idConsole}', 'Autor', null, 0, NOW(), NOW()),
            (100007, 1, '{$idConsole}', '{$idConsole}', 'Social', null, 0, NOW(), NOW()),
            (101000, 11, '{$idConsole}', '{$idConsole}', 'Scraper', null, 0, NOW(), NOW());
        "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM `asset_licence` WHERE id IN (100001, 100002, 100003, 100004, 100005, 100006, 100007, 101000)
        ');
        $this->addSql('
            DELETE FROM `ext_system` WHERE id IN (10, 11)
        ');
    }
}
