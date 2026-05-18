<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * TTS (Synthetic Audio) feature — T4.1 schema.
 *
 * Creates:
 *   - voice_family    : TTS voice family (slug + ext_system unique key)
 *   - voice           : concrete voice binding per provider
 *   - job_audio_narration : TTS job subtype (JOINED inheritance from job)
 *
 * DDL-only; non-transactional (ALGORITHM=INSTANT for ALTER).
 */
final class Version20260512173737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'TTS feature: voice_family, voice, job_audio_narration tables';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE voice_family (
                slug              VARCHAR(120)  NOT NULL,
                display_name      VARCHAR(255)  NOT NULL,
                language          VARCHAR(16)   NOT NULL,
                preferred_provider VARCHAR(255) DEFAULT NULL,
                active            TINYINT       NOT NULL,
                id                CHAR(36)      NOT NULL,
                created_at        DATETIME      NOT NULL,
                modified_at       DATETIME      NOT NULL,
                ext_system_id     INT           NOT NULL,
                created_by_id     INT           DEFAULT NULL,
                modified_by_id    INT           DEFAULT NULL,
                INDEX IDX_6220A527B03A8386 (created_by_id),
                INDEX IDX_6220A52799049ECE (modified_by_id),
                INDEX IDX_voice_family_ext_system (ext_system_id),
                UNIQUE INDEX UNIQ_voice_family_ext_system_slug (ext_system_id, slug),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql(
            'CREATE TABLE voice (
                provider           VARCHAR(255) NOT NULL,
                external_voice_id  VARCHAR(255) NOT NULL,
                metadata           JSON         NOT NULL,
                main               TINYINT      NOT NULL,
                active             TINYINT      NOT NULL,
                id                 CHAR(36)     NOT NULL,
                created_at         DATETIME     NOT NULL,
                modified_at        DATETIME     NOT NULL,
                voice_family_id    CHAR(36)     NOT NULL,
                created_by_id      INT          DEFAULT NULL,
                modified_by_id     INT          DEFAULT NULL,
                INDEX IDX_E7FB583BB03A8386 (created_by_id),
                INDEX IDX_E7FB583B99049ECE (modified_by_id),
                INDEX IDX_voice_family (voice_family_id),
                INDEX IDX_provider (provider),
                UNIQUE INDEX UNIQ_voice_family_provider (voice_family_id, provider),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql(
            'CREATE TABLE job_audio_narration (
                open_initial_key                       VARCHAR(64)  DEFAULT NULL,
                mode                                   VARCHAR(255) NOT NULL,
                stable_asset_id                        CHAR(36)     DEFAULT NULL,
                asset_licence_id                       CHAR(36)     DEFAULT NULL,
                voice_family_slug                      VARCHAR(120) DEFAULT NULL,
                title                                  VARCHAR(255) DEFAULT NULL,
                cancel_requested                       TINYINT      DEFAULT 0 NOT NULL,
                failure_reason                         LONGTEXT     DEFAULT NULL,
                ext_ref_ext_resource_name              VARCHAR(64)  DEFAULT NULL,
                ext_ref_ext_id                         VARCHAR(255) DEFAULT NULL,
                ext_ref_ext_version                    VARCHAR(64)  DEFAULT NULL,
                source_text                            LONGTEXT     DEFAULT NULL,
                source_hash                            VARCHAR(64)  DEFAULT NULL,
                podcast_options_auto_podcast_id        CHAR(36)     DEFAULT NULL,
                podcast_options_recommended_podcast_id CHAR(36)     DEFAULT NULL,
                podcast_options_include_in_recommended TINYINT      DEFAULT 0 NOT NULL,
                id                                     INT          NOT NULL,
                INDEX IDX_job_audio_narration_ext (ext_ref_ext_resource_name, ext_ref_ext_id),
                INDEX IDX_job_audio_narration_stable_asset (stable_asset_id),
                UNIQUE INDEX UNIQ_job_audio_narration_open_initial_key (open_initial_key),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );
        $this->addSql(
            'ALTER TABLE job_audio_narration
                ADD CONSTRAINT FK_A35F63B4BF396750 FOREIGN KEY (id) REFERENCES job (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE voice
                ADD CONSTRAINT FK_E7FB583BB35DFD53 FOREIGN KEY (voice_family_id) REFERENCES voice_family (id) ON DELETE CASCADE'
        );
        $this->addSql('ALTER TABLE voice ADD CONSTRAINT FK_E7FB583BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE voice ADD CONSTRAINT FK_E7FB583B99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE voice_family ADD CONSTRAINT FK_6220A527E961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE voice_family ADD CONSTRAINT FK_6220A527B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE voice_family ADD CONSTRAINT FK_6220A52799049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE job_audio_narration DROP FOREIGN KEY FK_A35F63B4BF396750');
        $this->addSql('ALTER TABLE voice DROP FOREIGN KEY FK_E7FB583BB35DFD53');
        $this->addSql('ALTER TABLE voice DROP FOREIGN KEY FK_E7FB583BB03A8386');
        $this->addSql('ALTER TABLE voice DROP FOREIGN KEY FK_E7FB583B99049ECE');
        $this->addSql('ALTER TABLE voice_family DROP FOREIGN KEY FK_6220A527E961F7A');
        $this->addSql('ALTER TABLE voice_family DROP FOREIGN KEY FK_6220A527B03A8386');
        $this->addSql('ALTER TABLE voice_family DROP FOREIGN KEY FK_6220A52799049ECE');
        $this->addSql('DROP TABLE job_audio_narration');
        $this->addSql('DROP TABLE voice');
        $this->addSql('DROP TABLE voice_family');
    }
}
