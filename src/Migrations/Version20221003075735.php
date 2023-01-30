<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221003075735 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $idAnonymous = App::getUserIdAnonymous();
        $idConsole = App::getUserIdConsole();
        $this->addSql("INSERT INTO `user` (id, email, first_name, last_name, roles, permissions, enabled, created_by_id, modified_by_id, created_at, modified_at, allowed_asset_external_providers, allowed_distribution_services) VALUES
            ('{$idAnonymous}', 'anzu.app.anonym@smeonline.sk', 'Anonymous', 'DAM', '[\"ROLE_USER\"]', '[]', 0, '{$idAnonymous}', '{$idAnonymous}', NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY()),                                                       
            ('{$idConsole}', 'anzu.app.console@smeonline.sk', 'Console', 'DAM', '[\"ROLE_USER\"]', '[]', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY())                                                       
        ");

        $this->addSql(
            "INSERT INTO `ext_system` (id, `name`, `slug`, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (1, 'CMS system', 'cms', '{$idConsole}', '{$idConsole}', NOW(), NOW()),
            (4, 'Blog system', 'blog', '{$idConsole}', '{$idConsole}', NOW(), NOW());
        "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id IN (1,2)');
    }
}
