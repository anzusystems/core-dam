<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240311111727 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $userId = App::getUserIdConsole();
        $extSystemId = 1;

        $this->addSql("
            INSERT INTO custom_form_element (id, form_id, created_by_id, modified_by_id, name, exif_autocomplete,
                                          created_at, modified_at, position, attributes_type, attributes_min_value,
                                          attributes_max_value, attributes_min_count, attributes_max_count,
                                          attributes_required, attributes_searchable, attributes_readonly, property)
            VALUES (
                '{$generateUuid()}', 
                (
                    select id
                    from custom_form
                    where resource_key = 'distribution_service_artemis_podcast_cms'
                      and ext_system_id = {$extSystemId}
                      and dtype = 'resourcecustomform'
                    limit 1
                ),
                 '{$userId}', 
                 '{$userId}', 
                'Externá URL epizódy',
                 '[]',
                NOW(), 
                NOW(), 
                14, 
                'string',
                 null, 
                 2048, 
                 null, 
                 null, 
                 0, 
                 0,
                 0,
                'bonusUrl'
            );
      ");
        $this->addSql("
            INSERT INTO custom_form_element (id, form_id, created_by_id, modified_by_id, name, exif_autocomplete,
                                          created_at, modified_at, position, attributes_type, attributes_min_value,
                                          attributes_max_value, attributes_min_count, attributes_max_count,
                                          attributes_required, attributes_searchable, attributes_readonly, property)
            VALUES (
                '{$generateUuid()}', 
                (
                    select id
                    from custom_form
                    where resource_key = 'distribution_service_artemis_podcast_cms'
                      and ext_system_id = {$extSystemId}
                      and dtype = 'resourcecustomform'
                    limit 1
                ),
                 '{$userId}', 
                 '{$userId}', 
                'Dĺžka bonus audia',
                 '[]',
                NOW(), 
                NOW(), 
                15, 
                'integer',
                 0, 
                 null, 
                 null, 
                 null, 
                 0, 
                 0,
                 0,
                'bonusDuration'
            );
      ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
