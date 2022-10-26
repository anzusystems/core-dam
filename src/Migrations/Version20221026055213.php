<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221026055213 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {

        $this->addSql('CREATE TABLE asset_custom_form (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', ext_system_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, asset_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D8866CBBE961F7A (ext_system_id), INDEX IDX_D8866CBBB03A8386 (created_by_id), INDEX IDX_D8866CBB99049ECE (modified_by_id), UNIQUE INDEX UNIQ_asset_type_ext_system (asset_type, ext_system_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset_custom_form ADD CONSTRAINT FK_D8866CBBE961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id)');
        $this->addSql('ALTER TABLE asset_custom_form ADD CONSTRAINT FK_D8866CBBB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE asset_custom_form ADD CONSTRAINT FK_D8866CBB99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE custom_form_element DROP FOREIGN KEY FK_BAB29195FF69B7D');
        $this->addSql('ALTER TABLE custom_form_element ADD CONSTRAINT FK_BAB29195FF69B7D FOREIGN KEY (form_id) REFERENCES asset_custom_form (id)');
        $this->addSql('ALTER TABLE custom_form DROP FOREIGN KEY FK_53FE35B299049ECE');
        $this->addSql('ALTER TABLE custom_form DROP FOREIGN KEY FK_53FE35B2B03A8386');
        $this->addSql('ALTER TABLE custom_form DROP FOREIGN KEY FK_53FE35B2E961F7A');
        $this->addSql('DROP TABLE custom_form');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_form_element DROP FOREIGN KEY FK_BAB29195FF69B7D');
        $this->addSql('CREATE TABLE custom_form (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', ext_system_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, asset_type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_asset_type_ext_system (asset_type, ext_system_id), INDEX IDX_53FE35B2E961F7A (ext_system_id), INDEX IDX_53FE35B2B03A8386 (created_by_id), INDEX IDX_53FE35B299049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE custom_form ADD CONSTRAINT FK_53FE35B299049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE custom_form ADD CONSTRAINT FK_53FE35B2B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE custom_form ADD CONSTRAINT FK_53FE35B2E961F7A FOREIGN KEY (ext_system_id) REFERENCES ext_system (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE asset_custom_form DROP FOREIGN KEY FK_D8866CBBE961F7A');
        $this->addSql('ALTER TABLE asset_custom_form DROP FOREIGN KEY FK_D8866CBBB03A8386');
        $this->addSql('ALTER TABLE asset_custom_form DROP FOREIGN KEY FK_D8866CBB99049ECE');
        $this->addSql('DROP TABLE asset_custom_form');
        $this->addSql('ALTER TABLE custom_form_element DROP FOREIGN KEY FK_BAB29195FF69B7D');
        $this->addSql('ALTER TABLE custom_form_element ADD CONSTRAINT FK_BAB29195FF69B7D FOREIGN KEY (form_id) REFERENCES custom_form (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
