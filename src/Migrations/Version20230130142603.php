<?php

declare(strict_types=1);

namespace App\Migrations;

use AnzuSystems\CommonBundle\Helper\PasswordHelper;
use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230130142603 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $token = PasswordHelper::passwordHash('TOKEN_sys_anzu_blog');
        $userId = App::getUserIdConsole();
        $this->addSql(<<<SQL
            INSERT INTO `user` SET
                id = 1963060,
                created_at = NOW(),
                modified_at = NOW(),
                created_by_id = '{$userId}',
                modified_by_id = '{$userId}',
                api_token = '{$token}',
                roles = '["ROLE_SYS_API"]',               
                permissions = JSON_OBJECT(),               
                allowed_asset_external_providers = JSON_ARRAY(),               
                allowed_distribution_services = JSON_ARRAY(),               
                email = 'sys_anzu_blog.anzu@smeonline.sk',               
                enabled = 0,               
                person_first_name = 'Blog SYS',               
                person_last_name = 'ANZU',
                person_full_name = 'Blog SYS ANZU', 
                avatar_color = '#8C9419',
                avatar_text = 'AB'
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM `user` WHERE id = 1963060 LIMIT 1');
    }
}
