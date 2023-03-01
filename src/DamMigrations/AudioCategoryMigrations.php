<?php

declare(strict_types=1);

namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use App\App;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Symfony\Component\Uid\Uuid;
use Throwable;

final class AudioCategoryMigrations extends AbstractMigrations
{
    public function migrate(MigrateConfig $migrateConfig): void
    {
        $this->migrateAudioCategories();
        $this->migrateVideoCategories();
    }

    public function getOrCreateOption(string $categorySelectId, string $value, string $title): string
    {
        $optionId = $this->defaultConnection->fetchOne(
            'SELECT id FROM distribution_category_option WHERE select_id = ? AND value = ?',
            [
                $categorySelectId,
                $value,
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
                    'name' => $title,
                    'value' => $value,
                    'position' => 0,
                    'assignable' => 1,
                ]
            );

            return (string) $id;
        }

        return $optionId;
    }

    public function getOrCreateCategorySelect(AssetType $type, string $serviceSlug): string
    {
        $categorySelectId = $this->defaultConnection->fetchOne(
            '
            SELECT
                id, ext_system_id, created_by_id, modified_by_id, service_slug, type, created_at, modified_at
            FROM distribution_category_select
            WHERE 
                service_slug = ? and type = ? and ext_system_id = ?',
            [
                $serviceSlug,
                $type->toString(),
                self::CMS_EXT_SYSTEM_ID,
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
                    'type' => $type->toString(),
                    'ext_system_id' => self::CMS_EXT_SYSTEM_ID,
                ]
            );

            return (string) $id;
        }

        return (string) $categorySelectId;
    }

    public function getOrCreateCategory(
        array $row,
        AssetType $type
    ): string {
        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM distribution_category where name = ? AND type = ? AND ext_system_id = ?',
            [
                $row['title'],
                $type->toString(),
                self::CMS_EXT_SYSTEM_ID,
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
                    'created_by_id' => $row['created_by_id'],
                    'modified_by_id' => $row['modified_by_id'],
                    'name' => $row['title'],
                    'type' => $type->toString(),
                    'ext_system_id' => self::CMS_EXT_SYSTEM_ID,
                ]
            );

            return (string) $id;
        }

        return $id;
    }

    /**
     * @throws Exception
     */
    private function migrateAudioCategories(): void
    {
        $this->outputUtil->info('Migrate Audio categories');
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->start();

        try {
            $this->defaultConnection->beginTransaction();

            $categorySelectId = $this->getOrCreateCategorySelect(AssetType::Audio, 'artemis_podcast_cms');
            foreach ($this->getCategories() as $category) {
                $categoryId = $this->getOrCreateCategory($category, AssetType::Audio);
                $optionId = $this->getOrCreateOption($categorySelectId, (string) $category['rubricId'], (string) $category['rubricTitle']);
                $this->assignOptionToCategory($categoryId, $optionId);
                $progressBar->advance();
            }

            $this->defaultConnection->commit();
        } catch (Throwable) {
            $this->defaultConnection->rollBack();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function migrateVideoCategories(): void
    {
        $this->outputUtil->info('Migrate Video categories');
        $progressBar = $this->outputUtil->createProgressBar();
        $progressBar->start();

        try {
            $ytCmsMainSelectId = $this->getOrCreateCategorySelect(AssetType::Video, 'youtube_cms_main');
            $jwCmsSelectId = $this->getOrCreateCategorySelect(AssetType::Video, 'jw_cms');
            $artemisCmsSelectId = $this->getOrCreateCategorySelect(AssetType::Video, 'artemis_cms');

            foreach ($this->getVideoCategories() as $videoCategory) {
                $categoryId = $this->getOrCreateCategory($videoCategory, AssetType::Video);

                if ($videoCategory['youtube_category_id']) {
                    $optionId = $this->getOrCreateOption($ytCmsMainSelectId, (string) $videoCategory['youtube_category_id'], (string) $videoCategory['ytTitle']);
                    $this->assignOptionToCategory($categoryId, $optionId);
                }
                if ($videoCategory['jw_video_category_id']) {
                    $optionId = $this->getOrCreateOption($jwCmsSelectId, (string) $videoCategory['jw_video_category_id'], (string) $videoCategory['jvcTitle']);
                    $this->assignOptionToCategory($categoryId, $optionId);
                }
                if ($videoCategory['artemis_rubric_id']) {
                    $optionId = $this->getOrCreateOption($artemisCmsSelectId, (string) $videoCategory['artemis_rubric_id'], (string) $videoCategory['rubricTitle']);
                    $this->assignOptionToCategory($categoryId, $optionId);
                }
                $progressBar->advance();
            }
        } catch (Throwable) {
            $this->defaultConnection->rollBack();
        }

        $progressBar->finish();
        $this->outputUtil->writeln('');
    }

    private function assignOptionToCategory(string $categoryId, string $optionId): void
    {
        $result = $this->defaultConnection->fetchOne(
            'SELECT distribution_category_id FROM distribution_category_has_selected_option WHERE distribution_category_id = ? AND distribution_category_option_id = ?',
            [
                $categoryId,
                $optionId,
            ]
        );

        if (false === $result) {
            $this->defaultConnection->insert(
                'distribution_category_has_selected_option',
                [
                    'distribution_category_id' => $categoryId,
                    'distribution_category_option_id' => $optionId,
                ]
            );
        }
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

    private function getVideoCategories(): array
    {
        return $this->damLegacyConnection->fetchAllAssociative(
            '
            SELECT 
                vc.id, 
                vc.title, 
                vc.youtube_category_id, 
                vc.jw_video_category_id, 
                vc.artemis_rubric_id, 
                vc.created_by_id, 
                vc.modified_by_id, 
                vc.created_at, 
                vc.modified_at, 
                ar.title as rubricTitle,
                ar.distribution_id as rubricId,
                ar.section_id as sectionId,
                jvc.title as jvcTitle,
                yc.distribution_id as ytDistributionId,
                yc.title as ytTitle,
                yc.assignable as ytAssignable
            FROM video_category vc
            LEFT JOIN artemis_rubric ar ON ar.id = vc.artemis_rubric_id
            LEFT JOIN jw_video_category jvc ON jvc.id = vc.jw_video_category_id
            LEFT JOIN youtube_category yc ON yc.id = vc.youtube_category_id
        '
        );
    }
}
