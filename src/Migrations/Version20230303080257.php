<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230303080257 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $imageFormId = $generateUuid();
        $videoFormId = $generateUuid();
        $audioFormId = $generateUuid();
        $documentFormId = $generateUuid();
        $userId = App::getUserIdConsole();

        foreach ($this->getCustomForms($imageFormId, $videoFormId, $audioFormId, $documentFormId) as $formData) {
            $this->addSql(
                "INSERT INTO custom_form (id, created_by_id, modified_by_id, ext_system_id, created_at, modified_at, 
                         dtype, asset_type) 
                VALUES (:id, '{$userId}', '{$userId}', 1,  NOW(),NOW(), 'assetcustomform', :asset_type);",
                $formData
            );
        }

        foreach ($this->getFormElements($imageFormId, $videoFormId, $audioFormId, $documentFormId) as $formData) {
            $this->addSql(
                "INSERT INTO custom_form_element (id, form_id, created_by_id, modified_by_id, `name`, key_name, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable)
                VALUES ('{$generateUuid()}', :form_id, '{$userId}', '{$userId}', :name, :key_name, :exif_autocomplete, NOW(),NOW(),
                        :position, :attributes_type, :attributes_min_value, :attributes_max_value, :attributes_min_count,
                        :attributes_max_count, :attributes_required, :attributes_searchable);",
                $formData
            );
        }
    }

    public function down(Schema $schema): void
    {
    }

    private function getFormElements(
        string $imageFormId,
        string $videoFormId,
        string $audioFormId,
        string $documentFormId
    ): array {
        return [
            ...$this->getImageFormElements($imageFormId),
            ...$this->getVideoFormElements($videoFormId),
            ...$this->getAudioFormElements($audioFormId),
            ...$this->getDocumentFormElements($documentFormId),
        ];
    }

    private function getCustomForms(
        string $imageFormId,
        string $videoFormId,
        string $audioFormId,
        string $documentFormId
    ): array {
        return [
            [
                'id' => $imageFormId,
                'asset_type' => 'image',
            ],
            [
                'id' => $videoFormId,
                'asset_type' => 'video',
            ],
            [
                'id' => $audioFormId,
                'asset_type' => 'audio',
            ],
            [
                'id' => $documentFormId,
                'asset_type' => 'document',
            ],
        ];
    }

    private function getImageFormElements(string $customFormId): array
    {
        return [
            [
                'form_id' => $customFormId,
                'name' => 'Title',
                'key_name' => 'title',
                'exif_autocomplete' => '["Title", "Subject"]',
                'position' => 2,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 256,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'Description',
                'key_name' => 'description',
                'exif_autocomplete' => '["Description", "ImageDescription"]',
                'position' => 3,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 2_000,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'Media API Ids',
                'key_name' => 'mediaApiIds',
                'exif_autocomplete' => '[]',
                'position' => 10,
                'attributes_type' => 'string_array',
                'attributes_min_value' => null,
                'attributes_max_value' => 128,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 0,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'Media API URLs',
                'key_name' => 'mediaApiPaths',
                'exif_autocomplete' => '[]',
                'position' => 11,
                'attributes_type' => 'string_array',
                'attributes_min_value' => null,
                'attributes_max_value' => 128,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 0,
            ],
        ];
    }

    private function getVideoFormElements(string $customFormId): array
    {
        return [
            [
                'form_id' => $customFormId,
                'name' => 'Title',
                'key_name' => 'title',
                'exif_autocomplete' => '["Title", "Subject"]',
                'position' => 2,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 256,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'Description',
                'key_name' => 'description',
                'exif_autocomplete' => '["Description", "ImageDescription"]',
                'position' => 3,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 5_000,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
        ];
    }

    private function getDocumentFormElements(string $customFormId): array
    {
        return [
            [
                'form_id' => $customFormId,
                'name' => 'Title',
                'key_name' => 'title',
                'exif_autocomplete' => '["Title", "Subject"]',
                'position' => 2,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 256,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'Description',
                'key_name' => 'description',
                'exif_autocomplete' => '["Description", "ImageDescription"]',
                'position' => 3,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 2_000,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
        ];
    }

    private function getAudioFormElements(string $customFormId): array
    {
        return [
            [
                'form_id' => $customFormId,
                'name' => 'Title',
                'key_name' => 'title',
                'exif_autocomplete' => '["Title", "Subject"]',
                'position' => 2,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 256,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'Description',
                'key_name' => 'description',
                'exif_autocomplete' => '["Description", "ImageDescription"]',
                'position' => 3,
                'attributes_type' => 'string',
                'attributes_min_value' => null,
                'attributes_max_value' => 5_000,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 1,
            ],
        ];
    }
}
