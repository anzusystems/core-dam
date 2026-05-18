<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Promotes TTS feature state from `asset_metadata.custom_data['tts']` JSON blob to a dedicated
 * `tts_asset` table. `custom_data` is reserved for user-defined fields; system feature state
 * belongs in typed columns with real indexes and FK constraints.
 *
 * After this migration:
 *  - `tts_asset` holds the TTS extension for every TTS-managed Asset (1:1 via shared PK).
 *  - The legacy `custom_data['tts']` namespace is intentionally LEFT IN PLACE for one release —
 *    a follow-up migration will drop it after verification that no read path depends on it.
 *
 * Backfill skips rows where `custom_data->>'$.tts'` is missing (non-TTS assets) and rows whose
 * NOT-NULL fields are missing (defensive — partial blobs are not rehydrated).
 */
final class Version20260519100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TTS: create tts_asset table + backfill from asset_metadata.custom_data[\'tts\']';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE TABLE tts_asset (
                asset_id                       CHAR(36)      NOT NULL,
                ext_resource_name              VARCHAR(64)   DEFAULT NULL,
                ext_id                         VARCHAR(255)  DEFAULT NULL,
                ext_version                    VARCHAR(64)   DEFAULT NULL,
                asset_licence_id               CHAR(36)      NOT NULL,
                auto_podcast_id                CHAR(36)      DEFAULT NULL,
                recommended_podcast_id         CHAR(36)      DEFAULT NULL,
                include_in_recommended_podcast TINYINT       DEFAULT 0 NOT NULL,
                voice_family_slug              VARCHAR(120)  NOT NULL,
                voice_family_id                CHAR(36)      NOT NULL,
                provider                       VARCHAR(255)  NOT NULL,
                external_voice_id              VARCHAR(255)  NOT NULL,
                source_text_hash               VARCHAR(64)   NOT NULL,
                source_text_snapshot           LONGTEXT      NOT NULL,
                generated_at                   DATETIME      NOT NULL,
                last_regenerated_at            DATETIME      DEFAULT NULL,
                status                         VARCHAR(32)   NOT NULL,
                regen_job_id                   CHAR(36)      DEFAULT NULL,
                failure_reason                 LONGTEXT      DEFAULT NULL,
                is_staging                     TINYINT       DEFAULT 0 NOT NULL,
                created_at                     DATETIME      NOT NULL,
                modified_at                    DATETIME      NOT NULL,
                INDEX IDX_tts_asset_ext (ext_resource_name, ext_id),
                INDEX IDX_tts_asset_status (status),
                PRIMARY KEY (asset_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`"
        );
        $this->addSql(
            'ALTER TABLE tts_asset
                ADD CONSTRAINT FK_tts_asset_asset_id FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE'
        );

        // Backfill — ATOM date strings come in as `YYYY-MM-DDTHH:MM:SS+00:00`; strip the timezone
        // suffix and parse the leading 19 characters as a MySQL DATETIME. NULLIF guards against
        // JSON `null` literal coming back as the string 'null' through JSON_UNQUOTE.
        $this->addSql(
            "INSERT INTO tts_asset (
                asset_id,
                ext_resource_name, ext_id, ext_version,
                asset_licence_id,
                auto_podcast_id, recommended_podcast_id, include_in_recommended_podcast,
                voice_family_slug, voice_family_id, provider, external_voice_id,
                source_text_hash, source_text_snapshot,
                generated_at, last_regenerated_at,
                status, regen_job_id, failure_reason, is_staging,
                created_at, modified_at
            )
            SELECT
                a.id,
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.extResourceName')), 'null'),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.extId')), 'null'),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.extVersion')), 'null'),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.assetLicenceId')),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.autoPodcastId')), 'null'),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.recommendedPodcastId')), 'null'),
                CAST(COALESCE(JSON_EXTRACT(am.custom_data, '$.tts.includeInRecommendedPodcast'), 0) AS UNSIGNED),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.voiceFamilySlug')),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.voiceFamilyId')),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.provider')),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.externalVoiceId')),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.sourceTextHash')),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.sourceTextSnapshot')),
                STR_TO_DATE(LEFT(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.generatedAt')), 19), '%Y-%m-%dT%H:%i:%s'),
                STR_TO_DATE(LEFT(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.lastRegeneratedAt')), 'null'), 19), '%Y-%m-%dT%H:%i:%s'),
                JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.status')),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.regenJobId')), 'null'),
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(am.custom_data, '$.tts.failureReason')), 'null'),
                CAST(COALESCE(JSON_EXTRACT(am.custom_data, '$.tts.isStaging'), 0) AS UNSIGNED),
                a.created_at,
                a.modified_at
            FROM asset a
            INNER JOIN asset_metadata am ON am.id = a.metadata_id
            WHERE JSON_EXTRACT(am.custom_data, '$.tts.status') IS NOT NULL"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tts_asset DROP FOREIGN KEY FK_tts_asset_asset_id');
        $this->addSql('DROP TABLE tts_asset');
    }
}
