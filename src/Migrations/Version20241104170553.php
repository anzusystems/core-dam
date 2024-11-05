<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241104170553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100014, 1, '{$idConsole}', '{$idConsole}', 'Accelerated Widget legacy', null, 0, NOW(), NOW())
            "
        );
    }

    public function down(Schema $schema): void
    {
    }
}
