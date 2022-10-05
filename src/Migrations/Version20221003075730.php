<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221003075730 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', metadata_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', licence_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', texts_title VARCHAR(255) NOT NULL, texts_display_title VARCHAR(255) NOT NULL, texts_description VARCHAR(5000) NOT NULL, dates_uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', dates_expire_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', dates_publish_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', asset_flags_described TINYINT(1) NOT NULL, asset_flags_visible TINYINT(1) NOT NULL, asset_flags_with_processed_file TINYINT(1) NOT NULL, asset_flags_autocompleted_metadata TINYINT(1) NOT NULL, asset_flags_auto_delete_unprocessed TINYINT(1) NOT NULL, attributes_asset_type VARCHAR(255) NOT NULL, attributes_status VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2AF5A5CDC9EE959 (metadata_id), INDEX IDX_2AF5A5C26EF07C9 (licence_id), INDEX IDX_2AF5A5CB03A8386 (created_by_id), INDEX IDX_2AF5A5C99049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', metadata_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', origin_asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', asset_attributes_checksum VARCHAR(64) NOT NULL, asset_attributes_file_path VARCHAR(255) NOT NULL, asset_attributes_origin_file_name VARCHAR(255) NOT NULL, asset_attributes_mime_type VARCHAR(32) NOT NULL, asset_attributes_size BIGINT NOT NULL, asset_attributes_uploaded_size BIGINT NOT NULL, asset_attributes_origin_url VARCHAR(2048) DEFAULT NULL, asset_attributes_status VARCHAR(255) NOT NULL, asset_attributes_fail_reason VARCHAR(255) NOT NULL, flags_processed_metadata TINYINT(1) NOT NULL, dtype VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_68FBF3D8DC9EE959 (metadata_id), INDEX IDX_68FBF3D8366C73B1 (origin_asset_id), INDEX IDX_68FBF3D8B03A8386 (created_by_id), INDEX IDX_68FBF3D899049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_file_metadata (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, exif_data JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3D3162E8B03A8386 (created_by_id), INDEX IDX_3D3162E899049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_has_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', audio_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', video_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', document_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', version_title VARCHAR(255) NOT NULL, is_default TINYINT(1) NOT NULL, INDEX IDX_53A8F7CF5DA1941 (asset_id), UNIQUE INDEX UNIQ_53A8F7CF3DA5256D (image_id), UNIQUE INDEX UNIQ_53A8F7CF3A3123C7 (audio_id), UNIQUE INDEX UNIQ_53A8F7CF29C1004E (video_id), UNIQUE INDEX UNIQ_53A8F7CFC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_licence (id INT AUTO_INCREMENT NOT NULL, ext_system_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, ext_id VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7E44A71EE961F7A (ext_system_id), INDEX IDX_7E44A71EB03A8386 (created_by_id), INDEX IDX_7E44A71E99049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_metadata (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, custom_data JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CD7F2E94B03A8386 (created_by_id), INDEX IDX_CD7F2E9499049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audio_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', attributes_duration DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE author (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', first_name VARCHAR(120) NOT NULL, last_name VARCHAR(120) NOT NULL, full_name VARCHAR(240) NOT NULL, INDEX IDX_BDAFD8C8B03A8386 (created_by_id), INDEX IDX_BDAFD8C899049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chunk (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', asset_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', offset INT NOT NULL, size INT NOT NULL, mime_type VARCHAR(32) NOT NULL, file_path VARCHAR(255) NOT NULL, created_at DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', checksum VARCHAR(64) NOT NULL, INDEX IDX_9506712EF025383A (asset_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_form (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', ext_system_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, asset_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_53FE35B2E961F7A (ext_system_id), INDEX IDX_53FE35B2B03A8386 (created_by_id), INDEX IDX_53FE35B299049ECE (modified_by_id), UNIQUE INDEX UNIQ_asset_type_ext_system (asset_type, ext_system_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE custom_form_element (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', form_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, key_name VARCHAR(255) NOT NULL, exif_autocomplete JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', position SMALLINT NOT NULL, attributes_type VARCHAR(255) NOT NULL, attributes_min_value INT DEFAULT NULL, attributes_max_value INT DEFAULT NULL, attributes_min_count INT DEFAULT NULL, attributes_max_count INT DEFAULT NULL, attributes_required TINYINT(1) NOT NULL, attributes_searchable TINYINT(1) NOT NULL, INDEX IDX_BAB29195FF69B7D (form_id), INDEX IDX_BAB2919B03A8386 (created_by_id), INDEX IDX_BAB291999049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE document_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', attributes_page_count SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_system (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1A377093B03A8386 (created_by_id), INDEX IDX_1A37709399049ECE (modified_by_id), UNIQUE INDEX UNIQ_slug (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', image_attributes_ratio_width INT NOT NULL, image_attributes_ratio_height INT NOT NULL, image_attributes_width INT NOT NULL, image_attributes_height INT NOT NULL, image_attributes_rotation SMALLINT NOT NULL, image_attributes_most_dominant_color VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE image_file_optimal_resize (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', requested_size INT NOT NULL, width INT NOT NULL, height INT NOT NULL, file_path VARCHAR(255) NOT NULL, INDEX IDX_73F73AAE3DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE keyword (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5A93713BB03A8386 (created_by_id), INDEX IDX_5A93713B99049ECE (modified_by_id), UNIQUE INDEX UNIQ_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission_group (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(2000) NOT NULL, permissions JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_BB4729B62B36786B (title), INDEX IDX_BB4729B6B03A8386 (created_by_id), INDEX IDX_BB4729B699049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_token (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', user_id INT DEFAULT NULL, device_id VARCHAR(36) NOT NULL, token_hash VARCHAR(255) NOT NULL, ip_address VARCHAR(45) NOT NULL, device_info JSON NOT NULL, issued_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C74F2195A76ED395 (user_id), UNIQUE INDEX UNIQ_C74F2195A76ED39594A4C7D4 (user_id, device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region_of_interest (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, point_x INT NOT NULL, point_y INT NOT NULL, percentage_width DOUBLE PRECISION NOT NULL, percentage_height DOUBLE PRECISION NOT NULL, title VARCHAR(128) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', position SMALLINT NOT NULL, INDEX IDX_789D9563DA5256D (image_id), INDEX IDX_789D956B03A8386 (created_by_id), INDEX IDX_789D95699049ECE (modified_by_id), INDEX IDX_position (position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) DEFAULT NULL, api_token VARCHAR(255) DEFAULT NULL, permissions JSON NOT NULL, roles JSON NOT NULL, enabled TINYINT(1) NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dam_user_asset_licence (dam_user_id INT NOT NULL, asset_licence_id INT NOT NULL, INDEX IDX_39FB0A0CAE87B06A (dam_user_id), INDEX IDX_39FB0A0CE00EE493 (asset_licence_id), PRIMARY KEY(dam_user_id, asset_licence_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_permission_group (user_id INT NOT NULL, permission_group_id INT NOT NULL, INDEX IDX_C6526B6BA76ED395 (user_id), INDEX IDX_C6526B6BB6C0CF1 (permission_group_id), PRIMARY KEY(user_id, permission_group_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', attributes_width INT NOT NULL, attributes_height INT NOT NULL, attributes_rotation SMALLINT NOT NULL, attributes_duration DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5CDC9EE959 FOREIGN KEY (metadata_id) REFERENCES asset_metadata (id)');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C26EF07C9 FOREIGN KEY (licence_id) REFERENCES asset_licence (id)');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_2AF5A5C99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_file ADD CONSTRAINT FK_68FBF3D8DC9EE959 FOREIGN KEY (metadata_id) REFERENCES asset_file_metadata (id)');
        $this->addSql('ALTER TABLE asset_file ADD CONSTRAINT FK_68FBF3D8366C73B1 FOREIGN KEY (origin_asset_id) REFERENCES asset_file (id)');
        $this->addSql('ALTER TABLE asset_file ADD CONSTRAINT FK_68FBF3D8B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_file ADD CONSTRAINT FK_68FBF3D899049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_file_metadata ADD CONSTRAINT FK_3D3162E8B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_file_metadata ADD CONSTRAINT FK_3D3162E899049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF3DA5256D FOREIGN KEY (image_id) REFERENCES image_file (id)');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF3A3123C7 FOREIGN KEY (audio_id) REFERENCES audio_file (id)');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CF29C1004E FOREIGN KEY (video_id) REFERENCES video_file (id)');
        $this->addSql('ALTER TABLE asset_has_file ADD CONSTRAINT FK_53A8F7CFC33F7837 FOREIGN KEY (document_id) REFERENCES document_file (id)');
        $this->addSql('ALTER TABLE asset_licence ADD CONSTRAINT FK_7E44A71EE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE asset_licence ADD CONSTRAINT FK_7E44A71EB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_licence ADD CONSTRAINT FK_7E44A71E99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_metadata ADD CONSTRAINT FK_CD7F2E94B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_metadata ADD CONSTRAINT FK_CD7F2E9499049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE audio_file ADD CONSTRAINT FK_C32E2A4CBF396750 FOREIGN KEY (id) REFERENCES asset_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE author ADD CONSTRAINT FK_BDAFD8C8B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE author ADD CONSTRAINT FK_BDAFD8C899049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chunk ADD CONSTRAINT FK_9506712EF025383A FOREIGN KEY (asset_file_id) REFERENCES asset_file (id)');
        $this->addSql('ALTER TABLE custom_form ADD CONSTRAINT FK_53FE35B2E961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE custom_form ADD CONSTRAINT FK_53FE35B2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE custom_form ADD CONSTRAINT FK_53FE35B299049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE custom_form_element ADD CONSTRAINT FK_BAB29195FF69B7D FOREIGN KEY (form_id) REFERENCES custom_form (id)');
        $this->addSql('ALTER TABLE custom_form_element ADD CONSTRAINT FK_BAB2919B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE custom_form_element ADD CONSTRAINT FK_BAB291999049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE document_file ADD CONSTRAINT FK_2B2BBA83BF396750 FOREIGN KEY (id) REFERENCES asset_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ext_system ADD CONSTRAINT FK_1A377093B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ext_system ADD CONSTRAINT FK_1A37709399049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE image_file ADD CONSTRAINT FK_7EA5DC8EBF396750 FOREIGN KEY (id) REFERENCES asset_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE image_file_optimal_resize ADD CONSTRAINT FK_73F73AAE3DA5256D FOREIGN KEY (image_id) REFERENCES image_file (id)');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713BB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE keyword ADD CONSTRAINT FK_5A93713B99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE permission_group ADD CONSTRAINT FK_BB4729B6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE permission_group ADD CONSTRAINT FK_BB4729B699049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE region_of_interest ADD CONSTRAINT FK_789D9563DA5256D FOREIGN KEY (image_id) REFERENCES image_file (id)');
        $this->addSql('ALTER TABLE region_of_interest ADD CONSTRAINT FK_789D956B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE region_of_interest ADD CONSTRAINT FK_789D95699049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dam_user_asset_licence ADD CONSTRAINT FK_39FB0A0CAE87B06A FOREIGN KEY (dam_user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dam_user_asset_licence ADD CONSTRAINT FK_39FB0A0CE00EE493 FOREIGN KEY (asset_licence_id) REFERENCES asset_licence (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_permission_group ADD CONSTRAINT FK_C6526B6BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_permission_group ADD CONSTRAINT FK_C6526B6BB6C0CF1 FOREIGN KEY (permission_group_id) REFERENCES permission_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video_file ADD CONSTRAINT FK_8B086BCCBF396750 FOREIGN KEY (id) REFERENCES asset_file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5CDC9EE959');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C26EF07C9');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5CB03A8386');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_2AF5A5C99049ECE');
        $this->addSql('ALTER TABLE asset_file DROP FOREIGN KEY FK_68FBF3D8DC9EE959');
        $this->addSql('ALTER TABLE asset_file DROP FOREIGN KEY FK_68FBF3D8366C73B1');
        $this->addSql('ALTER TABLE asset_file DROP FOREIGN KEY FK_68FBF3D8B03A8386');
        $this->addSql('ALTER TABLE asset_file DROP FOREIGN KEY FK_68FBF3D899049ECE');
        $this->addSql('ALTER TABLE asset_file_metadata DROP FOREIGN KEY FK_3D3162E8B03A8386');
        $this->addSql('ALTER TABLE asset_file_metadata DROP FOREIGN KEY FK_3D3162E899049ECE');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF5DA1941');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF3DA5256D');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF3A3123C7');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CF29C1004E');
        $this->addSql('ALTER TABLE asset_has_file DROP FOREIGN KEY FK_53A8F7CFC33F7837');
        $this->addSql('ALTER TABLE asset_licence DROP FOREIGN KEY FK_7E44A71EE961F7A');
        $this->addSql('ALTER TABLE asset_licence DROP FOREIGN KEY FK_7E44A71EB03A8386');
        $this->addSql('ALTER TABLE asset_licence DROP FOREIGN KEY FK_7E44A71E99049ECE');
        $this->addSql('ALTER TABLE asset_metadata DROP FOREIGN KEY FK_CD7F2E94B03A8386');
        $this->addSql('ALTER TABLE asset_metadata DROP FOREIGN KEY FK_CD7F2E9499049ECE');
        $this->addSql('ALTER TABLE audio_file DROP FOREIGN KEY FK_C32E2A4CBF396750');
        $this->addSql('ALTER TABLE author DROP FOREIGN KEY FK_BDAFD8C8B03A8386');
        $this->addSql('ALTER TABLE author DROP FOREIGN KEY FK_BDAFD8C899049ECE');
        $this->addSql('ALTER TABLE chunk DROP FOREIGN KEY FK_9506712EF025383A');
        $this->addSql('ALTER TABLE custom_form DROP FOREIGN KEY FK_53FE35B2E961F7A');
        $this->addSql('ALTER TABLE custom_form DROP FOREIGN KEY FK_53FE35B2B03A8386');
        $this->addSql('ALTER TABLE custom_form DROP FOREIGN KEY FK_53FE35B299049ECE');
        $this->addSql('ALTER TABLE custom_form_element DROP FOREIGN KEY FK_BAB29195FF69B7D');
        $this->addSql('ALTER TABLE custom_form_element DROP FOREIGN KEY FK_BAB2919B03A8386');
        $this->addSql('ALTER TABLE custom_form_element DROP FOREIGN KEY FK_BAB291999049ECE');
        $this->addSql('ALTER TABLE document_file DROP FOREIGN KEY FK_2B2BBA83BF396750');
        $this->addSql('ALTER TABLE ext_system DROP FOREIGN KEY FK_1A377093B03A8386');
        $this->addSql('ALTER TABLE ext_system DROP FOREIGN KEY FK_1A37709399049ECE');
        $this->addSql('ALTER TABLE image_file DROP FOREIGN KEY FK_7EA5DC8EBF396750');
        $this->addSql('ALTER TABLE image_file_optimal_resize DROP FOREIGN KEY FK_73F73AAE3DA5256D');
        $this->addSql('ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713BB03A8386');
        $this->addSql('ALTER TABLE keyword DROP FOREIGN KEY FK_5A93713B99049ECE');
        $this->addSql('ALTER TABLE permission_group DROP FOREIGN KEY FK_BB4729B6B03A8386');
        $this->addSql('ALTER TABLE permission_group DROP FOREIGN KEY FK_BB4729B699049ECE');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395');
        $this->addSql('ALTER TABLE region_of_interest DROP FOREIGN KEY FK_789D9563DA5256D');
        $this->addSql('ALTER TABLE region_of_interest DROP FOREIGN KEY FK_789D956B03A8386');
        $this->addSql('ALTER TABLE region_of_interest DROP FOREIGN KEY FK_789D95699049ECE');
        $this->addSql('ALTER TABLE dam_user_asset_licence DROP FOREIGN KEY FK_39FB0A0CAE87B06A');
        $this->addSql('ALTER TABLE dam_user_asset_licence DROP FOREIGN KEY FK_39FB0A0CE00EE493');
        $this->addSql('ALTER TABLE user_permission_group DROP FOREIGN KEY FK_C6526B6BA76ED395');
        $this->addSql('ALTER TABLE user_permission_group DROP FOREIGN KEY FK_C6526B6BB6C0CF1');
        $this->addSql('ALTER TABLE video_file DROP FOREIGN KEY FK_8B086BCCBF396750');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE asset_file');
        $this->addSql('DROP TABLE asset_file_metadata');
        $this->addSql('DROP TABLE asset_has_file');
        $this->addSql('DROP TABLE asset_licence');
        $this->addSql('DROP TABLE asset_metadata');
        $this->addSql('DROP TABLE audio_file');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE chunk');
        $this->addSql('DROP TABLE custom_form');
        $this->addSql('DROP TABLE custom_form_element');
        $this->addSql('DROP TABLE document_file');
        $this->addSql('DROP TABLE ext_system');
        $this->addSql('DROP TABLE image_file');
        $this->addSql('DROP TABLE image_file_optimal_resize');
        $this->addSql('DROP TABLE keyword');
        $this->addSql('DROP TABLE permission_group');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE region_of_interest');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE dam_user_asset_licence');
        $this->addSql('DROP TABLE user_permission_group');
        $this->addSql('DROP TABLE video_file');
    }
}
