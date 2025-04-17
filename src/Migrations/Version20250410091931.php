<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250410091931 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE user 
            SET roles = JSON_ARRAY_APPEND(roles, '$', 'ROLE_SYS_COPY_IMAGE_API')
            WHERE id = 100016
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE user 
            SET roles = JSON_REMOVE(roles, JSON_UNQUOTE(JSON_SEARCH(roles, 'one', 'ROLE_SYS_COPY_IMAGE_API'))) 
            WHERE id = 100016
        SQL);
    }

}
