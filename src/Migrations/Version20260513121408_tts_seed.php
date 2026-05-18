<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * TTS (Synthetic Audio) feature — T4.2 seed DML.
 *
 * Seeds:
 *   - ext_system id=15 slug='cms_tts' — owner for TTS VoiceFamily + Assets
 *   - asset_licence id=100150 'CMS TTS Audio' — default TTS asset licence
 *   - podcast 'Všetko audio' (auto) — every generated TTS asset auto-enrolled
 *   - podcast 'SME výber' (recommended) — optional recommended-podcast membership
 *
 * Production-required for Phase 2 CMS integration.
 * Phase 1 pilot: enables TtsVoiceFixtures to look up the parent ExtSystem.
 *
 * Re-run safe: all INSERTs use ON DUPLICATE KEY UPDATE slug=slug / name=name
 * so the migration is idempotent if rows already exist (e.g. previous partial deploy).
 *
 * Multi-tenant note: this seed is for the SME tenant Phase 1 pilot.
 * Other tenants provision their own ext_system + licence via normal admin UI.
 */
final class Version20260513121408_tts_seed extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TTS seed DML: ext_system cms_tts (id=15), asset_licence audio-obsah (id=100150), 2 TTS podcasts';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        $generateUuid = fn (): string => uuid_create();

        // --- ExtSystem cms_tts ---
        $this->addSql(
            "INSERT INTO `ext_system` (id, name, slug, flags_check_image_used_on_delete, created_by_id, modified_by_id, created_at, modified_at)
            VALUES (15, 'CMS TTS Audio', 'cms_tts', 0, {$idConsole}, {$idConsole}, NOW(), NOW())
            ON DUPLICATE KEY UPDATE slug = slug"
        );

        // --- AssetLicence audio-obsah (belongs to ext_system id=15) ---
        $this->addSql(
            "INSERT INTO `asset_licence` (id, ext_system_id, name, ext_id, limited_files,
                internal_rule_active, internal_rule_mark_as_internal_since,
                created_by_id, modified_by_id, created_at, modified_at)
            VALUES (100150, 15, 'CMS TTS Audio', null, 0, 0, null, {$idConsole}, {$idConsole}, NOW(), NOW())
            ON DUPLICATE KEY UPDATE name = name"
        );

        // --- Podcast 'Všetko audio' (auto — every TTS asset auto-enrolled) ---
        $autoPodcastId = $generateUuid();
        $this->addSql(
            "INSERT INTO `podcast` (
                id, licence_id, image_preview_id, alt_image_id,
                created_by_id, modified_by_id, created_at, modified_at,
                texts_title, texts_description,
                dates_import_from,
                attributes_rss_url, attributes_file_slot, attributes_ext_url,
                attributes_last_import_status, attributes_mode,
                attributes_web_order_position, attributes_mobile_order_position,
                flags_web_public_export_enabled, flags_mobile_public_export_enabled
            ) SELECT
                '{$autoPodcastId}', 100150, null, null,
                {$idConsole}, {$idConsole}, NOW(), NOW(),
                'Všetko audio', '',
                NOW(),
                null, '', '',
                'none', 'default',
                0, 0,
                0, 0
            WHERE NOT EXISTS (
                SELECT 1 FROM podcast p
                JOIN asset_licence al ON p.licence_id = al.id
                WHERE al.ext_system_id = 15 AND p.attributes_mode = 'default' AND p.texts_title = 'Všetko audio'
            )"
        );

        // --- Podcast 'SME výber' (recommended — optional membership) ---
        $recommendedPodcastId = $generateUuid();
        $this->addSql(
            "INSERT INTO `podcast` (
                id, licence_id, image_preview_id, alt_image_id,
                created_by_id, modified_by_id, created_at, modified_at,
                texts_title, texts_description,
                dates_import_from,
                attributes_rss_url, attributes_file_slot, attributes_ext_url,
                attributes_last_import_status, attributes_mode,
                attributes_web_order_position, attributes_mobile_order_position,
                flags_web_public_export_enabled, flags_mobile_public_export_enabled
            ) SELECT
                '{$recommendedPodcastId}', 100150, null, null,
                {$idConsole}, {$idConsole}, NOW(), NOW(),
                'SME výber', '',
                NOW(),
                null, '', '',
                'none', 'default',
                0, 0,
                0, 0
            WHERE NOT EXISTS (
                SELECT 1 FROM podcast p
                JOIN asset_licence al ON p.licence_id = al.id
                WHERE al.ext_system_id = 15 AND p.texts_title = 'SME výber'
            )"
        );
    }

    public function down(Schema $schema): void
    {
        // Delete in FK-safe order: podcasts → asset_licence → ext_system.
        // ON DELETE for podcast.licence_id is no cascade (nullable FK) so manual cleanup is safe.
        $this->addSql(
            'DELETE p FROM podcast p
            JOIN asset_licence al ON p.licence_id = al.id
            WHERE al.ext_system_id = 15'
        );
        $this->addSql('DELETE FROM asset_licence WHERE id = 100150');
        $this->addSql("DELETE FROM ext_system WHERE id = 15 AND slug = 'cms_tts'");
    }
}
