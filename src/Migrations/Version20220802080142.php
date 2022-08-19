<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220802080142 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            INSERT INTO user (
                id, roles, enabled
            )   
            VALUES 
               (1763600,  \'["ROLE_ADMIN"]\', 1),
               (1000000,  \'["ROLE_ADMIN"]\', 1)
       ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user WHERE id IN (1763600, 1000000)');
    }
}
