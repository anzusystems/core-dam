<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250218105429 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100015, 1, '{$idConsole}', '{$idConsole}', 'Sportnet', null, 0, NOW(), NOW())
            "
        );
    }

    public function down(Schema $schema): void
    {
    }
}
