<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230127154302 extends AbstractMigration
{
    private const UNSPLASH_AUTHOR_ID = '0185f3e8-7f5e-750e-9075-29cdd412b988';

    public function up(Schema $schema): void
    {
        $id = self::UNSPLASH_AUTHOR_ID;
        $userId = App::getUserIdConsole();
        $extSystemId = 1;
        $this->addSql("INSERT INTO author (
                id, 
                ext_system_id, 
                created_by_id, 
                modified_by_id, 
                `name`, 
                identifier, 
                `type`,
                flags_reviewed,
                created_at,
                modified_at
            ) VALUES (
                '{$id}',
                '{$extSystemId}',
                '{$userId}',      
                '{$userId}',      
                'Unsplash',      
                '',      
                'agency',      
                1,
                NOW(),      
                NOW()      
            )
        ");
    }

    public function down(Schema $schema): void
    {
        $id = self::UNSPLASH_AUTHOR_ID;
        $this->addSql("DELETE FROM author WHERE id = '{$id}' LIMIT 1");
    }
}
