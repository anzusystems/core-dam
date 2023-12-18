<?php

declare(strict_types=1);

namespace App\Migrations;

use AnzuSystems\CommonBundle\Helper\PasswordHelper;
use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231218085634 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $scraperHash = PasswordHelper::passwordHash('TOKEN_sys_scraper');

        $this->addSql("INSERT INTO user (
            id, email, person_first_name, person_last_name, person_full_name, avatar_color, avatar_text, roles,
            permissions, enabled, created_by_id, modified_by_id, created_at, modified_at,
            allowed_asset_external_providers, allowed_distribution_services, api_token)
        VALUES
            (100005, 'sys_anzu_scraper.anzu@smeonline.sk', 'Scraper', 'Scraper', 'Scraper', '#9D1508', 'SR', '[\"ROLE_SYS_API\"]',
            '{\"dam_asset_create\":2,\"dam_asset_view\":2}', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(),
            JSON_ARRAY(), JSON_ARRAY(), '{$scraperHash}'
         ) ON DUPLICATE KEY UPDATE id = id
        ");

        $this->addSql('INSERT INTO users_to_ext_systems (user_id, ext_system_id) VALUES (100005, 11)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user WHERE id = 100005');
    }
}
