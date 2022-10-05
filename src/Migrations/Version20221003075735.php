<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221003075735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `user` (id, email, roles, permissions, enabled, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (1, 'dam_anonymous@anzusystems.dev', '[\"ROLE_USER\"]', '[]', 0, 1, 1, NOW(), NOW()),                                                       
            (2, 'dam_console@anzusystems.dev', '[\"ROLE_USER\"]', '[]', 0, 1, 1, NOW(), NOW())                                                       
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id IN (1,2)');
    }
}
