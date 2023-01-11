<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221230133349 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $blogCustomFormId = $generateUuid();
        $this->addSql("INSERT INTO custom_form (
                id, 
                created_by_id, 
                modified_by_id, 
                ext_system_id, 
                created_at, 
                modified_at, 
                dtype, 
                asset_type
              ) VALUES (
                '{$blogCustomFormId}', 
                1, 
                1, 
                4, 
                NOW(), 
                NOW(), 
                'assetcustomform', 
                'image'
              );
        ");
        $this->addSql("INSERT INTO custom_form_element (
                id, 
                form_id, 
                created_by_id, 
                modified_by_id, 
                `name`, 
                key_name, 
                exif_autocomplete, 
                created_at, 
                modified_at, 
                `position`, 
                attributes_type, 
                attributes_min_value, 
                attributes_max_value, 
                attributes_min_count, 
                attributes_max_count, 
                attributes_required, 
                attributes_searchable
            ) VALUES (
                '{$generateUuid()}', 
                '{$blogCustomFormId}', 
                1, 
                1, 
                'Description', 
                'description', 
                '[\"Description\", \"ImageDescription\"]', 
                NOW(), 
                NOW(), 
                2, 
                'string',
                 null, 
                 2000, 
                 null, 
                 null, 
                 0, 
                 1
            ), (
                '{$generateUuid()}', 
                '{$blogCustomFormId}', 
                1, 
                1, 
                'Author', 
                'author', 
                '[\"Author\"]', 
                NOW(), 
                NOW(), 
                3, 
                'string',
                 null, 
                 64, 
                 null, 
                 null, 
                 0, 
                 1
            );
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM custom_form_element 
            WHERE form_id = (SELECT id FROM custom_form WHERE ext_system_id = 4 AND asset_type = 'image') 
            AND key_name IN ('title', 'description', 'author')
        ");
    }
}
