<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230112084550 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $artemisCustomId = $generateUuid();
        $userId = App::getUserIdConsole();
        $extSystemId = 1;
        $this->addSql("INSERT INTO custom_form (
                id, 
                created_by_id, 
                modified_by_id, 
                ext_system_id, 
                created_at, 
                modified_at, 
                dtype, 
                resource_key
              ) VALUES (
                '{$artemisCustomId}', 
                '{$userId}', 
                '{$userId}', 
                '{$extSystemId}', 
                NOW(), 
                NOW(), 
                'resourcecustomform', 
                'distribution_service_artemis_podcast_cms'
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
                '{$artemisCustomId}', 
                '{$userId}', 
                '{$userId}',
                'Založiť článok', 
                'createArticle', 
                '[]', 
                NOW(), 
                NOW(), 
                5, 
                'boolean',
                 null, 
                 null, 
                 null, 
                 null, 
                 1, 
                 0
            ) ,  (
                '{$generateUuid()}', 
                '{$artemisCustomId}', 
                '{$userId}', 
                '{$userId}',
                'Titulok', 
                'title', 
                '[]', 
                NOW(), 
                NOW(), 
                1, 
                'string',
                 1, 
                 255, 
                 null, 
                 null, 
                 1, 
                 0
            ),  (
                '{$generateUuid()}', 
                '{$artemisCustomId}', 
                '{$userId}', 
                '{$userId}',
                'Popis', 
                'description', 
                '[]', 
                NOW(), 
                NOW(), 
                2, 
                'string',
                 null, 
                 5000, 
                 null, 
                 null, 
                 0, 
                 0
            ), (
                '{$generateUuid()}', 
                '{$artemisCustomId}', 
                '{$userId}', 
                '{$userId}',
                'Autori', 
                'authors', 
                '[]', 
                NOW(), 
                NOW(), 
                4, 
                'string_array',
                 null, 
                 255, 
                 null, 
                 32, 
                 0, 
                 0
            ), (
                '{$generateUuid()}', 
                '{$artemisCustomId}', 
                '{$userId}', 
                '{$userId}',
                'Kľúčové slová', 
                'keywords', 
                '[]', 
                NOW(), 
                NOW(), 
                3, 
                'string_array',
                 null, 
                 255, 
                 1, 
                 32, 
                 1, 
                 0
            );
        ");
    }

    public function down(Schema $schema): void
    {
    }
}
