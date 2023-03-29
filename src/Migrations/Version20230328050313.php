<?php

declare(strict_types=1);

namespace App\Migrations;

use AnzuSystems\CommonBundle\Helper\PasswordHelper;
use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230328050313 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $healthcheckToken = PasswordHelper::passwordHash('TOKEN_sys_health_check');
        $this->addSql("INSERT INTO `user` (id, email, person_first_name, person_last_name, person_full_name, avatar_color, avatar_text, roles, permissions, enabled, created_by_id, modified_by_id, created_at, modified_at, allowed_asset_external_providers, allowed_distribution_services, api_token) VALUES
            (1849357, 'sys_health_check.anzu@smeonline.sk', 'Healthcheck SYS', 'ANZU', 'Healthcheck SYS ANZU', '#9D1407', 'HS', '[\"ROLE_SYS_API\"]', '[]', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(), JSON_ARRAY(), JSON_ARRAY(), '{$healthcheckToken}')                                                       
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE from user where id = 1849357');
    }
}
