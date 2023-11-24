<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231123185947 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $generateUuid = fn () => uuid_create();
        $scraperCustomFormId = $generateUuid();
        $pressNewsCustomFormId = $generateUuid();
        $userId = App::getUserIdConsole();

        $this->addSql("
            INSERT INTO custom_form (
                id, created_by_id, modified_by_id, ext_system_id, created_at, modified_at, dtype, asset_type
            ) VALUES (
                '{$scraperCustomFormId}', '{$userId}', '{$userId}', 11, NOW(), NOW(), 'assetcustomform', 'image'
            ) , (
                '{$pressNewsCustomFormId}', '{$userId}', '{$userId}', 10, NOW(), NOW(), 'assetcustomform', 'image'
            );
        ");

        foreach ([$scraperCustomFormId, $pressNewsCustomFormId] as $formId) {
            $this->addSql(
                "INSERT INTO custom_form_element (form_id, id, created_by_id, modified_by_id, `name`, property, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable, attributes_readonly )
                VALUES (:form_id, '{$generateUuid()}', '{$userId}', '{$userId}', :name, :property,
                        :exif_autocomplete, NOW(),NOW(), :position, :attributes_type, 
                        :attributes_min_value, :attributes_max_value, :attributes_min_count, :attributes_max_count,
                        :attributes_required, :attributes_searchable, :attributes_readonly)",
                $this->getImageFormElements($formId)
            );
        }
    }

    public function down(Schema $schema): void
    {
    }

    private function getImageFormElements(string $customFormId): array
    {
        return [
            'form_id' => $customFormId,
            'name' => 'Názov',
            'property' => 'title',
            'exif_autocomplete' => '[]',
            'position' => 1,
            'attributes_type' => 'number',
            'attributes_min_value' => null,
            'attributes_max_value' => 255,
            'attributes_min_count' => null,
            'attributes_max_count' => null,
            'attributes_required' => 0,
            'attributes_searchable' => 1,
            'attributes_readonly' => 0,
        ];
    }
}
