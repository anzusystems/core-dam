<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230418055359 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `user` SET selected_licence_id = 100000 WHERE selected_licence_id IS NULL AND (JSON_CONTAINS(roles, '\"ROLE_ADMIN\"', '$') = 1 OR JSON_CONTAINS(roles, '\"ROLE_DAM_ADMIN\"', '$') = 1)");
    }

    public function down(Schema $schema): void
    {
    }
}
