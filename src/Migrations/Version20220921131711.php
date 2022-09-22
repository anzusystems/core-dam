<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220921131711 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `user` (id, email, roles, permissions, enabled) VALUES
            (1, 'dam_anonymous@anzusystems.dev', '[\"ROLE_USER\"]', '[]', 0),                                                       
            (2, 'dam_console@anzusystems.dev', '[\"ROLE_USER\"]', '[]', 0)                                                       
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id IN (1,2)');
    }
}
