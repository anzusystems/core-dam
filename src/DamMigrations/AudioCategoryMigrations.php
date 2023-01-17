<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\App;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use App\Model\MigrateConfig;

final class AudioCategoryMigrations extends AbstractMigrations
{
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $categorySelectId = $this->getOrCreateCategorySelect();

        foreach ($this->getCategories() as $category) {
            $categoryId = $this->getOrCreateCategory($category);
            $optionId = $this->getOrCreateOption($categorySelectId, $category);
            $this->assignOptionToCategory($categoryId, $optionId);
        }
    }

    private function assignOptionToCategory(string $categoryId, string $optionId): void
    {
        $result = $this->defaultConnection->fetchOne(
            'SELECT distribution_category_id FROM distribution_category_has_selected_option WHERE distribution_category_id = ? AND distribution_category_option_id = ?',
            [
                $categoryId,
                $optionId
            ]
        );

        if (false === $result) {
            $this->defaultConnection->insert(
                'distribution_category_has_selected_option',
                [
                    'distribution_category_id' => $categoryId,
                    'distribution_category_option_id' => $optionId
                ]
            );
        }
    }

    public function getOrCreateOption(string $categorySelectId, array $row): string
    {
        $optionId = $this->defaultConnection->fetchOne(
            'SELECT id FROM distribution_category_option WHERE select_id = ? AND value = ?',
            [
                $categorySelectId,
                $row['rubricId']
            ]
        );

        if (false === $optionId) {
            $id = Uuid::v6();
            $this->defaultConnection->insert(
                'distribution_category_option',
                [
                    'id' => $id,
                    'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                    'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                    'created_by_id' => App::getUserIdConsole(),
                    'modified_by_id' => App::getUserIdConsole(),
                    'select_id' => $categorySelectId,
                    'name' => $row['rubricTitle'],
                    'value' => $row['rubricId'],
                    'position' => 0,
                    'assignable' => 1
                ]
            );

            return (string) $id;
        }

        return $optionId;
    }

    public function getOrCreateCategorySelect(): string
    {
        $type = AssetType::Audio->toString();
        $serviceSlug = 'artemis_podcast_cms';

        $categorySelectId = $this->defaultConnection->fetchOne(
            '
            SELECT
                id, ext_system_id, created_by_id, modified_by_id, service_slug, type, created_at, modified_at
            FROM distribution_category_select
            WHERE 
                service_slug = ? and type = ? and ext_system_id = ?',
            [
                $serviceSlug,
                $type,
                self::CMS_EXT_SYSTEM_ID
            ]
        );

        if (false === $categorySelectId) {
            $id = Uuid::v6();
            $this->defaultConnection->insert(
                'distribution_category_select',
                [
                    'id' => $id,
                    'created_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                    'modified_at' => App::getAppDate()->format(DateTimeImmutable::ATOM),
                    'created_by_id' => App::getUserIdConsole(),
                    'modified_by_id' => App::getUserIdConsole(),
                    'service_slug' => $serviceSlug,
                    'type' => $type,
                    'ext_system_id' => self::CMS_EXT_SYSTEM_ID
                ]
            );

            return (string) $id;
        }

        return (string)$categorySelectId;
    }

    public function getOrCreateCategory(
        array $row
    ): string {
        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM distribution_category where name = ? AND type = ? AND ext_system_id = ?',
            [
                $row['title'],
                AssetType::Audio->toString(),
                self::CMS_EXT_SYSTEM_ID
            ]
        );

        if (false === $id) {
            $id = Uuid::v6();
            $this->defaultConnection->insert(
                'distribution_category',
                [
                    'id' => $id,
                    'created_at' => $row['created_at'],
                    'modified_at' => $row['modified_at'],
                    'created_by_id' => $this->getUserIdBySsoId((int)$row['created_by_id']),
                    'modified_by_id' => $this->getUserIdBySsoId((int)$row['modified_by_id']),
                    'name' => $row['title'],
                    'type' => AssetType::Audio->toString(),
                    'ext_system_id' => self::CMS_EXT_SYSTEM_ID
                ]
            );

            return (string) $id;
        }

        return $id;
    }

    private function getCategories(): array
    {
        return $this->damLegacyConnection->fetchAllAssociative(
            '
            SELECT 
                ac.id, 
                ac.created_by_id, 
                ac.modified_by_id,
                ac.title,
                ac.created_at, 
                ac.modified_at, 
                ac.artemis_rubric_id,
                ar.title as rubricTitle,
                ar.distribution_id as rubricId,
                ar.section_id as sectionId
            FROM audio_category ac
            INNER JOIN artemis_rubric ar ON ar.id = ac.artemis_rubric_id
        '
        );
    }
}
