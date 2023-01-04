<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221003075735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `user` (id, email, first_name, last_name, roles, permissions, enabled, created_by_id, modified_by_id, created_at, modified_at, allowed_asset_external_providers, allowed_distribution_services) VALUES
            (1, 'dam_anonymous@anzusystems.dev', 'Anonymous', 'DAM', '[\"ROLE_USER\"]', '[]', 0, 1, 1, NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY()),                                                       
            (2, 'dam_console@anzusystems.dev', 'Console', 'DAM', '[\"ROLE_USER\"]', '[]', 0, 1, 1, NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY())                                                       
        ");

        $this->addSql(
            'INSERT INTO `ext_system` (id, `name`, `slug`, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (1, \'CMS system\', \'cms\', 1, 1, NOW(), NOW()),
            (4, \'Blog system\', \'blog\', 1, 1, NOW(), NOW());
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id IN (1,2)');
    }
}
