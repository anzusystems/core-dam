<?php

declare(strict_types=1);

namespace App\Migrations;

use AnzuSystems\CommonBundle\Helper\PasswordHelper;
use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230614090051 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $infraHash = PasswordHelper::passwordHash('TOKEN_sys_haproxy');
        $this->addSql("INSERT INTO `user` (id, email, person_first_name, person_last_name, person_full_name, avatar_color, avatar_text, roles, permissions, enabled, created_by_id, modified_by_id, created_at, modified_at, allowed_asset_external_providers, allowed_distribution_services, api_token) VALUES
            (2009753, 'sys_anzu_haproxy.anzu@smeonline.sk', 'Infra SYS', 'INFRA', 'INFRA SYS ANZU', '#9D1508', 'IF', '[\"ROLE_SYS_API\"]', '[]', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY(), '{$infraHash}')                                                       
        ");

        $this->addSql(
            "INSERT INTO `keyword` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, flags_reviewed, created_at, modified_at) VALUES
            ( '1ee0b4fa-f21d-6794-8abb-153fe932206a', 1, '{$idConsole}', '{$idConsole}', 'live_import_restream', 1, NOW(), NOW());
        "
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id IN (2009753)');
    }
}
