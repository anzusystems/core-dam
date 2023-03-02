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
        $this->addSql("INSERT INTO `user` (id, email, person_first_name, person_last_name, person_full_name, avatar_color, avatar_text, roles, permissions, enabled, created_by_id, modified_by_id, created_at, modified_at, allowed_asset_external_providers, allowed_distribution_services) VALUES
            ('{$idAnonymous}', 'anzu.app.anonym@smeonline.sk', 'Anonymous', 'DAM', 'Anonymous DAM', '#2F2F2F', 'AD', '[\"ROLE_USER\"]', '[]', 0, '{$idAnonymous}', '{$idAnonymous}', NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY()),                                                       
            ('{$idConsole}', 'anzu.app.console@smeonline.sk', 'Console', 'DAM', 'Console DAM', '#2F2F2F', 'CD', '[\"ROLE_USER\"]', '[]', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY())                                                       
        ");

        $this->addSql(
            "INSERT INTO `ext_system` (id, `name`, `slug`, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (1, 'CMS system', 'cms', '{$idConsole}', '{$idConsole}', NOW(), NOW()),
            (4, 'Blog system', 'blog', '{$idConsole}', '{$idConsole}', NOW(), NOW()),
            (1000, 'Tools system', 'tools', '{$idConsole}', '{$idConsole}', NOW(), NOW());
        "
        );

        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (200000, 1000, '{$idConsole}', '{$idConsole}', 'Tools licence', '', 0, NOW(), NOW()),
            (100000, 1, '{$idConsole}', '{$idConsole}', 'CMS licence', '', 0, NOW(), NOW());
        "
        );

        $this->addSql(
            "INSERT INTO `keyword` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, flags_reviewed, created_at, modified_at) VALUES
            ( 'd84744d7-6e73-44ef-b038-29a829479c26', 1000, '{$idConsole}', '{$idConsole}', 'Blog', 1, NOW(), NOW());
        "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id IN (1,2)');
    }
}
