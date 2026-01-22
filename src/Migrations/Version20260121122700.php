<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260121122700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $idConsole = App::getUserIdConsole();

        $this->addSql(
            "INSERT INTO `ext_system` (id, `name`, `slug`, created_by_id, modified_by_id, created_at, modified_at) VALUES
            (14, 'euBrief', 'eu_brief', '{$idConsole}', '{$idConsole}', NOW(), NOW())
       "
        );

        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100140, 14, '{$idConsole}', '{$idConsole}', 'euBrief', null, 0, NOW(), NOW())
        "
        );

        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100141, 14, '{$idConsole}', '{$idConsole}', 'euBrief Autori', null, 0, NOW(), NOW())
        "
        );

        $this->addSql(
            "INSERT INTO `asset_licence` (id, `ext_system_id`, `created_by_id`, modified_by_id, name, ext_id, limited_files, created_at, modified_at) VALUES
            (100142, 14, '{$idConsole}', '{$idConsole}', 'euBrief Boxy', null, 0, NOW(), NOW())
        "
        );

        $imageFormId = $generateUuid();
        $this->addSql("
            INSERT INTO custom_form (
                id, created_by_id, modified_by_id, ext_system_id, created_at, modified_at, dtype, asset_type
            ) VALUES (
                '{$imageFormId}', '{$idConsole}', '{$idConsole}', 14, NOW(), NOW(), 'assetcustomform', 'image'
            );
        ");

        foreach ($this->getImageElements() as $element) {
            $this->addSql(
                "INSERT INTO custom_form_element (form_id, id, created_by_id, modified_by_id, `name`, property, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable, attributes_readonly )
                VALUES (
                        '{$imageFormId}', '{$generateUuid()}', '{$idConsole}', '{$idConsole}', :name, :property,
                        :exif_autocomplete, NOW(),NOW(), :position, :attributes_type, 
                        :attributes_min_value, :attributes_max_value, :attributes_min_count, :attributes_max_count,
                        :attributes_required, :attributes_searchable, :attributes_readonly)",
                $element
            );
        }

        $documentFormId = $generateUuid();
        $this->addSql("
            INSERT INTO custom_form (
                id, created_by_id, modified_by_id, ext_system_id, created_at, modified_at, dtype, asset_type
            ) VALUES (
                '{$documentFormId}', '{$idConsole}', '{$idConsole}', 14, NOW(), NOW(), 'assetcustomform', 'document'
            );
        ");

        foreach ($this->getDocumentElements() as $element) {
            $this->addSql(
                "INSERT INTO custom_form_element (form_id, id, created_by_id, modified_by_id, `name`, property, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable, attributes_readonly )
                VALUES (
                        '{$documentFormId}', '{$generateUuid()}', '{$idConsole}', '{$idConsole}', :name, :property,
                        :exif_autocomplete, NOW(),NOW(), :position, :attributes_type, 
                        :attributes_min_value, :attributes_max_value, :attributes_min_count, :attributes_max_count,
                        :attributes_required, :attributes_searchable, :attributes_readonly)",
                $element
            );
        }

        $idConsole = App::getUserIdConsole();
        $this->addSql("
            INSERT INTO author_clean_phrase(phrase, mode, type, author_replacement_id, ext_system_id, created_at,
                                            modified_at, created_by_id, modified_by_id, flags_word_boundary, position)
            VALUES  ('/', 'split', 'word', null, 14, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
                (',', 'split', 'word', null, 14, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
                (';', 'split', 'word', null, 14, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
                ('©', 'remove', 'word', null, 14, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100)
        ");
    }

    public function down(Schema $schema): void
    {
    }

    private function getImageElements(): array
    {
        return [
            [
                'name' => 'Popis',
                'property' => 'description',
                'exif_autocomplete' => '["Description", "ImageDescription", "Title", "Subject"]',
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
        ];
    }

    private function getDocumentElements(): array
    {
        return [
            [
                'name' => 'Titulok',
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
                'name' => 'Popis',
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
