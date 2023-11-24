<?php

declare(strict_types=1);

namespace App\Migrations;

use AnzuSystems\CommonBundle\Helper\PasswordHelper;
use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231027104219 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $mediaApiHash = PasswordHelper::passwordHash('TOKEN_sys_mediaapi');
        $artemisHash = PasswordHelper::passwordHash('TOKEN_sys_artemis');

        $this->addSql("INSERT INTO user (
            id, email, person_first_name, person_last_name, person_full_name, avatar_color, avatar_text, roles,
            permissions, enabled, created_by_id, modified_by_id, created_at, modified_at,
            allowed_asset_external_providers, allowed_distribution_services, api_token)
        VALUES
            (100004, 'sys_mediaapi@smeonline.sk', 'Mediaapi', 'Mediaapi', 'Mediaapi', '#9D1508', 'MA', '[\"ROLE_SYS_MEDIAAPI_API\"]',
             '[]', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(),
             JSON_ARRAY(), JSON_ARRAY(), '{$mediaApiHash}'
            ),
            (1825192, 'sys_artemis.anzu@smeonline.sk', 'Artemis', 'Artemis', 'Artemis', '#9D1508', 'AR', '[\"ROLE_SYS_ARTEMIS_API\"]',
            '[]', 0, '{$idConsole}', '{$idConsole}', NOW(), NOW(),
            JSON_ARRAY(), JSON_ARRAY(), '{$artemisHash}'
         ) ON DUPLICATE KEY UPDATE id = id
        ");

        $generateUuid = fn () => uuid_create();
        foreach ($this->getImageFormElements('74086a09-a15e-4b12-8683-925ec92a3527') as $formData) {
            $this->addSql(
                "INSERT INTO custom_form_element (form_id, id, created_by_id, modified_by_id, `name`, property, 
                                 exif_autocomplete, created_at, modified_at,  `position`, attributes_type, 
                                 attributes_min_value, attributes_max_value, attributes_min_count, attributes_max_count, 
                                 attributes_required, attributes_searchable, attributes_readonly )
                VALUES (
                        (SELECT id FROM custom_form WHERE ext_system_id = 1 AND dtype = 'assetcustomform' AND asset_type = 'image'),
                        '{$generateUuid()}', '{$idConsole}', '{$idConsole}', :name, :property, :exif_autocomplete, NOW(),NOW(),
                        :position, :attributes_type, :attributes_min_value, :attributes_max_value, :attributes_min_count,
                        :attributes_max_count, :attributes_required, :attributes_searchable, :attributes_readonly)",
                $formData
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user WHERE id = 100004');
        $this->addSql('DELETE FROM user WHERE id = 1825192');
    }

    private function getImageFormElements(string $customFormId): array
    {
        return [
            [
                'form_id' => $customFormId,
                'name' => 'MediaApi focusX',
                'property' => 'focusX',
                'exif_autocomplete' => '[]',
                'position' => 12,
                'attributes_type' => 'number',
                'attributes_min_value' => null,
                'attributes_max_value' => null,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 0,
                'attributes_readonly' => 1,
            ],
            [
                'form_id' => $customFormId,
                'name' => 'MediaApi focusY',
                'property' => 'focusY',
                'exif_autocomplete' => '[]',
                'position' => 13,
                'attributes_type' => 'number',
                'attributes_min_value' => null,
                'attributes_max_value' => null,
                'attributes_min_count' => null,
                'attributes_max_count' => null,
                'attributes_required' => 0,
                'attributes_searchable' => 0,
                'attributes_readonly' => 1,
            ],
        ];
    }
}
