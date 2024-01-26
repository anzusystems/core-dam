<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240126092223 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $idConsole = App::getUserIdConsole();

        $this->addSql(
            "INSERT INTO `ext_system` (id, `name`, `slug`, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (12, 'Osobnosti', 'people', '{$idConsole}', '{$idConsole}', NOW(), NOW())
       "
        );

        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100012, 12, '{$idConsole}', '{$idConsole}', 'Osobnosti', null, 0, NOW(), NOW())
        "
        );

        $formId = $generateUuid();
        $this->addSql("
            INSERT INTO custom_form (
                id, created_by_id, modified_by_id, ext_system_id, created_at, modified_at, dtype, asset_type
            ) VALUES (
                '{$formId}', '{$idConsole}', '{$idConsole}', 12, NOW(), NOW(), 'assetcustomform', 'image'
            );
        ");

        foreach ($this->getScraperElements() as $element) {
            $this->addSql(
                "INSERT INTO custom_form_element (form_id, id, created_by_id, modified_by_id, `name`, property, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable, attributes_readonly )
                VALUES (
                        '{$formId}', '{$generateUuid()}', '{$idConsole}', '{$idConsole}', :name, :property,
                        :exif_autocomplete, NOW(),NOW(), :position, :attributes_type, 
                        :attributes_min_value, :attributes_max_value, :attributes_min_count, :attributes_max_count,
                        :attributes_required, :attributes_searchable, :attributes_readonly)",
                $element
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('
            DELETE FROM `asset_licence` WHERE id = 100012
        ');
        $this->addSql('
            DELETE FROM `ext_system` WHERE id = 12
        ');
    }

    private function getScraperElements(): array
    {
        return [
            [
                'name' => 'Title',
                'property' => 'title',
                'exif_autocomplete' => '["Title", "Subject"]',
                'position' => 0,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 256,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
                'attributes_readonly' => 0,
            ],
            [
                'name' => 'Description',
                'property' => 'description',
                'exif_autocomplete' => '["Description", "ImageDescription"]',
                'position' => 1,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 2_000,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
                'attributes_readonly' => 0,
            ],
        ];
    }
}
