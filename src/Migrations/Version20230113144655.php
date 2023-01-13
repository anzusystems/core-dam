<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230113144655 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset_slot (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', audio_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', video_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', document_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', flags_is_default TINYINT(1) NOT NULL, flags_is_main TINYINT(1) NOT NULL, INDEX IDX_486AE5AF5DA1941 (asset_id), INDEX IDX_486AE5AF3DA5256D (image_id), INDEX IDX_486AE5AF3A3123C7 (audio_id), INDEX IDX_486AE5AF29C1004E (video_id), INDEX IDX_486AE5AFC33F7837 (document_id), INDEX IDX_486AE5AFB03A8386 (created_by_id), INDEX IDX_486AE5AF99049ECE (modified_by_id), INDEX IDX_name (name), INDEX IDX_default (flags_is_default), UNIQUE INDEX UNIQ_asset_file_asset_name (asset_id, name, image_id, audio_id, video_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AF5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AF3DA5256D FOREIGN KEY (image_id) REFERENCES image_file (id)');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AF3A3123C7 FOREIGN KEY (audio_id) REFERENCES audio_file (id)');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AF29C1004E FOREIGN KEY (video_id) REFERENCES video_file (id)');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AFC33F7837 FOREIGN KEY (document_id) REFERENCES document_file (id)');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AFB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_slot ADD CONSTRAINT FK_486AE5AF99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CFC33F7837');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF5DA1941');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF3DA5256D');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF3A3123C7');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF29C1004E');
        $this->addSql('DROP TABLE asset_has_file');
        $this->addSql('ALTER TABLE asset ADD main_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD asset_flags_generated_by_system TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C6780D085 FOREIGN KEY (main_file_id) REFERENCES asset_file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2AF5A5C6780D085 ON asset (main_file_id)');
        $this->addSql('ALTER TABLE asset_licence ADD limited_files TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE audio_file ADD asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE audio_file ADD CONSTRAINT FK_C32E2A4C5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_C32E2A4C5DA1941 ON audio_file (asset_id)');
        $this->addSql('ALTER TABLE document_file ADD asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE document_file ADD CONSTRAINT FK_2B2BBA835DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2B2BBA835DA1941 ON document_file (asset_id)');
        $this->addSql('ALTER TABLE image_file ADD asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE image_file ADD CONSTRAINT FK_7EA5DC8E5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7EA5DC8E5DA1941 ON image_file (asset_id)');
        $this->addSql('ALTER TABLE podcast ADD preview_image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE attributes_file_position attributes_file_slot VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDFAE957CD FOREIGN KEY (preview_image_id) REFERENCES asset (id)');
        $this->addSql('CREATE INDEX IDX_D7E805BDFAE957CD ON podcast (preview_image_id)');
        $this->addSql('ALTER TABLE podcast_episode ADD preview_image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD0FAE957CD FOREIGN KEY (preview_image_id) REFERENCES asset (id)');
        $this->addSql('CREATE INDEX IDX_77EB2BD0FAE957CD ON podcast_episode (preview_image_id)');
        $this->addSql('ALTER TABLE user ADD selected_licence_id INT DEFAULT NULL, DROP password');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649116F432A FOREIGN KEY (selected_licence_id) REFERENCES asset_licence (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649116F432A ON user (selected_licence_id)');
        $this->addSql('ALTER TABLE video_file ADD preview_image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', ADD asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE video_file ADD CONSTRAINT FK_8B086BCCFAE957CD FOREIGN KEY (preview_image_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE video_file ADD CONSTRAINT FK_8B086BCC5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_8B086BCCFAE957CD ON video_file (preview_image_id)');
        $this->addSql('CREATE INDEX IDX_8B086BCC5DA1941 ON video_file (asset_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset_has_file (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', asset_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', image_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', audio_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', video_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', document_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', version_title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, is_default TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_53A8F7CF3DA5256D (image_id), UNIQUE INDEX UNIQ_53A8F7CF3A3123C7 (audio_id), UNIQUE INDEX UNIQ_53A8F7CF29C1004E (video_id), UNIQUE INDEX UNIQ_53A8F7CFC33F7837 (document_id), INDEX IDX_53A8F7CF5DA1941 (asset_id), INDEX IDX_version_title (version_title), INDEX IDX_default (is_default), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CFC33F7837 FOREIGN KEY (document_id) REFERENCES document_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF3DA5256D FOREIGN KEY (image_id) REFERENCES image_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF3A3123C7 FOREIGN KEY (audio_id) REFERENCES audio_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF29C1004E FOREIGN KEY (video_id) REFERENCES video_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AF5DA1941');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AF3DA5256D');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AF3A3123C7');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AF29C1004E');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AFC33F7837');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AFB03A8386');
        $this->addSql('ALTER TABLE asset_slot DROP FOREIGN KEY FK_486AE5AF99049ECE');
        $this->addSql('DROP TABLE asset_slot');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C6780D085');
        $this->addSql('DROP INDEX UNIQ_2AF5A5C6780D085 ON asset');
        $this->addSql('ALTER TABLE asset DROP main_file_id, DROP asset_flags_generated_by_system');
        $this->addSql('ALTER TABLE asset_licence DROP limited_files');
        $this->addSql('ALTER TABLE audio_file DROP FOREIGN KEY FK_C32E2A4C5DA1941');
        $this->addSql('DROP INDEX IDX_C32E2A4C5DA1941 ON audio_file');
        $this->addSql('ALTER TABLE audio_file DROP asset_id');
        $this->addSql('ALTER TABLE document_file DROP FOREIGN KEY FK_2B2BBA835DA1941');
        $this->addSql('DROP INDEX IDX_2B2BBA835DA1941 ON document_file');
        $this->addSql('ALTER TABLE document_file DROP asset_id');
        $this->addSql('ALTER TABLE image_file DROP FOREIGN KEY FK_7EA5DC8E5DA1941');
        $this->addSql('DROP INDEX IDX_7EA5DC8E5DA1941 ON image_file');
        $this->addSql('ALTER TABLE image_file DROP asset_id');
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDFAE957CD');
        $this->addSql('DROP INDEX IDX_D7E805BDFAE957CD ON podcast');
        $this->addSql('ALTER TABLE podcast DROP preview_image_id, CHANGE attributes_file_slot attributes_file_position VARCHAR(128) DEFAULT NULL');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD0FAE957CD');
        $this->addSql('DROP INDEX IDX_77EB2BD0FAE957CD ON podcast_episode');
        $this->addSql('ALTER TABLE podcast_episode DROP preview_image_id');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649116F432A');
        $this->addSql('DROP INDEX IDX_8D93D649116F432A ON user');
        $this->addSql('ALTER TABLE user ADD password VARCHAR(255) DEFAULT NULL, DROP selected_licence_id');
        $this->addSql('ALTER TABLE video_file DROP FOREIGN KEY FK_8B086BCCFAE957CD');
        $this->addSql('ALTER TABLE video_file DROP FOREIGN KEY FK_8B086BCC5DA1941');
        $this->addSql('DROP INDEX IDX_8B086BCCFAE957CD ON video_file');
        $this->addSql('DROP INDEX IDX_8B086BCC5DA1941 ON video_file');
        $this->addSql('ALTER TABLE video_file DROP preview_image_id, DROP asset_id');
    }
}
