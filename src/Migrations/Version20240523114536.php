<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240523114536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update doctrine v2 -> v3 (drop comments)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'ALTER TABLE asset
                    CHANGE id id CHAR(36) NOT NULL,
                    CHANGE main_file_id main_file_id CHAR(36) DEFAULT NULL,
                    CHANGE metadata_id metadata_id CHAR(36) DEFAULT NULL,
                    CHANGE distribution_category_id distribution_category_id CHAR(36) DEFAULT NULL,
                    CHANGE created_at created_at DATETIME NOT NULL,
                    CHANGE modified_at modified_at DATETIME NOT NULL,
                    CHANGE dates_uploaded_at dates_uploaded_at DATETIME NOT NULL,
                    CHANGE dates_expire_at dates_expire_at DATETIME DEFAULT NULL,
                    CHANGE dates_publish_at dates_publish_at DATETIME DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_author
                    CHANGE asset_id asset_id CHAR(36) NOT NULL,
                    CHANGE author_id author_id CHAR(36) NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_keyword
                    CHANGE asset_id asset_id CHAR(36) NOT NULL,
                    CHANGE keyword_id keyword_id CHAR(36) NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_file
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE metadata_id metadata_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL,
                CHANGE asset_attributes_origin_external_provider asset_attributes_origin_external_provider VARCHAR(255) DEFAULT NULL,
                CHANGE asset_attributes_origin_storage asset_attributes_origin_storage VARCHAR(255) DEFAULT NULL,
                CHANGE main_route_id main_route_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_file_metadata
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_file_route
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE target_asset_file_id target_asset_file_id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_licence
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_licence_group
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_metadata
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE asset_slot
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL,
                CHANGE image_id image_id CHAR(36) DEFAULT NULL,
                CHANGE audio_id audio_id CHAR(36) DEFAULT NULL,
                CHANGE video_id video_id CHAR(36) DEFAULT NULL,
                CHANGE document_id document_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE audio_file
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE author
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE chunk
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE asset_file_id asset_file_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE custom_form
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE custom_form_element
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE form_id form_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE distribution
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE publish_at publish_at DATETIME DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL,
                CHANGE distribution_asset_id distribution_asset_id CHAR(36) DEFAULT NULL,
                CHANGE distribution_asset_file_id distribution_asset_file_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE distribution_distribution
                CHANGE distribution_source distribution_source CHAR(36) NOT NULL,
                CHANGE distribution_target distribution_target CHAR(36) NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE distribution_category
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE distribution_category_has_selected_option
                CHANGE distribution_category_id distribution_category_id CHAR(36) NOT NULL,
                CHANGE distribution_category_option_id distribution_category_option_id CHAR(36) NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE distribution_category_option
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE select_id select_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE distribution_category_select
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE document_file
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE ext_system
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE image_file
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL,
                CHANGE image_attributes_most_dominant_color image_attributes_most_dominant_color VARCHAR(255) NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE image_file_optimal_resize
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE image_id image_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE image_preview
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE image_file_id image_file_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE job
                CHANGE started_at started_at DATETIME DEFAULT NULL,
                CHANGE finished_at finished_at DATETIME DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL,
                CHANGE scheduled_at scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE keyword
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE permission_group
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE podcast
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE image_preview_id image_preview_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL,
                CHANGE dates_import_from dates_import_from DATETIME DEFAULT NULL,
                CHANGE alt_image_id alt_image_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE podcast_episode
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE image_preview_id image_preview_id CHAR(36) DEFAULT NULL,
                CHANGE podcast_id podcast_id CHAR(36) DEFAULT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL,
                CHANGE dates_publication_date dates_publication_date DATETIME DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE region_of_interest
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE image_id image_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE user
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE video_file
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE image_preview_id image_preview_id CHAR(36) DEFAULT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL'
        );
        $this->addSql(
            'ALTER TABLE video_show
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
        $this->addSql(
            'ALTER TABLE video_show_episode
                CHANGE id id CHAR(36) NOT NULL,
                CHANGE video_show_id video_show_id CHAR(36) DEFAULT NULL,
                CHANGE asset_id asset_id CHAR(36) DEFAULT NULL,
                CHANGE created_at created_at DATETIME NOT NULL,
                CHANGE modified_at modified_at DATETIME NOT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE asset CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE dates_uploaded_at dates_uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE dates_expire_at dates_expire_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE dates_publish_at dates_publish_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE main_file_id main_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE metadata_id metadata_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE distribution_category_id distribution_category_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_author CHANGE asset_id asset_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE author_id author_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_file CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE asset_attributes_origin_external_provider asset_attributes_origin_external_provider VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:OriginExternalProviderType)\', CHANGE asset_attributes_origin_storage asset_attributes_origin_storage VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:OriginStorageType)\', CHANGE metadata_id metadata_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE main_route_id main_route_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_file_metadata CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_file_route CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE target_asset_file_id target_asset_file_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_keyword CHANGE asset_id asset_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE keyword_id keyword_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_licence CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE asset_licence_group CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE asset_metadata CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE asset_slot CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE image_id image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE audio_id audio_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE video_id video_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE document_id document_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE audio_file ADD audio_public_link_path VARCHAR(255) DEFAULT \'\', ADD audio_public_link_slug VARCHAR(128) DEFAULT \'\', ADD audio_public_link_is_public TINYINT(1) DEFAULT 0, CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE author CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE chunk CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE asset_file_id asset_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE custom_form CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE custom_form_element CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE form_id form_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE distribution CHANGE publish_at publish_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE distribution_asset_id distribution_asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE distribution_asset_file_id distribution_asset_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE distribution_category CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE distribution_category_has_selected_option CHANGE distribution_category_id distribution_category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE distribution_category_option_id distribution_category_option_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE distribution_category_option CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE select_id select_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE distribution_category_select CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE distribution_distribution CHANGE distribution_source distribution_source CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE distribution_target distribution_target CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE document_file CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE ext_system CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE image_file CHANGE image_attributes_most_dominant_color image_attributes_most_dominant_color VARCHAR(255) NOT NULL COMMENT \'(DC2Type:ColorType)\', CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE image_file_optimal_resize CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE image_id image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE image_preview CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE image_file_id image_file_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE job CHANGE scheduled_at scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE started_at started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE finished_at finished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE keyword CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE permission_group CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE podcast CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE dates_import_from dates_import_from DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image_preview_id image_preview_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE alt_image_id alt_image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE podcast_episode CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE dates_publication_date dates_publication_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image_preview_id image_preview_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE podcast_id podcast_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE region_of_interest CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image_id image_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE video_file CHANGE image_preview_id image_preview_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE video_show CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE video_show_episode CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE video_show_id video_show_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', CHANGE asset_id asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
    }
}
