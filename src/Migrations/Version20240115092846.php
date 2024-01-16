<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240115092846 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $userId = App::getUserIdConsole();

        foreach ($this->getScraperElements() as $element) {
            $this->addSql(
                "INSERT INTO custom_form_element (form_id, id, created_by_id, modified_by_id, `name`, property, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable, attributes_readonly )
                VALUES (
                        (SELECT id FROM custom_form WHERE ext_system_id = 11 and asset_type = 'image' LIMIT 1)
                        , '{$generateUuid()}', '{$userId}', '{$userId}', :name, :property,
                        :exif_autocomplete, NOW(),NOW(), :position, :attributes_type, 
                        :attributes_min_value, :attributes_max_value, :attributes_min_count, :attributes_max_count,
                        :attributes_required, :attributes_searchable, :attributes_readonly)",
                $element
            );
        }

        $this->addSql(
            "DELETE cfe FROM custom_form_element cfe
                INNER JOIN custom_form cf ON cfe.form_id = cf.id
                WHERE cf.ext_system_id = 11 and cfe.property = 'title'
        "
        );
    }

    public function down(Schema $schema): void
    {
    }

    private function getScraperElements(): array
    {
        return [
            [
                'name' => 'Description',
                'property' => 'description',
                'exif_autocomplete' => '[]',
                'position' => 0,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 2_000,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
                'attributes_readonly' => 0,
            ],
            [
                'name' => 'Provider Type',
                'property' => 'providerType',
                'exif_autocomplete' => '[]',
                'position' => 1,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 31,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 1,
                'attributes_searchable' => 1,
                'attributes_readonly' => 0,
            ],
            [
                'name' => 'Original Url',
                'property' => 'originalUrl',
                'exif_autocomplete' => '[]',
                'position' => 2,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 2_048,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 1,
                'attributes_searchable' => 1,
                'attributes_readonly' => 0,
            ],
            [
                'name' => 'Provider ID',
                'property' => 'providerId',
                'exif_autocomplete' => '[]',
                'position' => 3,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 255,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 1,
                'attributes_searchable' => 1,
                'attributes_readonly' => 0,
            ],
            [
                'name' => 'Child',
                'property' => 'isChild',
                'exif_autocomplete' => '[]',
                'position' => 4,
                'attributes_type' => 'boolean',
                'attributes_min_value' => null,
                'attributes_max_value' => null,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 1,
                'attributes_searchable' => 0,
                'attributes_readonly' => 0,
            ],
        ];
    }
}
