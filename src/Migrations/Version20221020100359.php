<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221020100359 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE asset_author (asset_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', author_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_558257D55DA1941 (asset_id), INDEX IDX_558257D5F675F31B (author_id), PRIMARY KEY(asset_id, author_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_keyword (asset_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', keyword_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_397D306D5DA1941 (asset_id), INDEX IDX_397D306D115D4552 (keyword_id), PRIMARY KEY(asset_id, keyword_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset_author ADD CONSTRAINT FK_558257D55DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_author ADD CONSTRAINT FK_558257D5F675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_keyword ADD CONSTRAINT FK_397D306D5DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE asset_keyword ADD CONSTRAINT FK_397D306D115D4552 FOREIGN KEY (keyword_id) REFERENCES keyword (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_author DROP FOREIGN KEY FK_558257D55DA1941');
        $this->addSql('ALTER TABLE asset_author DROP FOREIGN KEY FK_558257D5F675F31B');
        $this->addSql('ALTER TABLE asset_keyword DROP FOREIGN KEY FK_397D306D5DA1941');
        $this->addSql('ALTER TABLE asset_keyword DROP FOREIGN KEY FK_397D306D115D4552');
        $this->addSql('DROP TABLE asset_author');
        $this->addSql('DROP TABLE asset_keyword');
    }
}
