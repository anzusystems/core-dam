<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202125114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE author_is_current_author (author_source CHAR(36) NOT NULL, author_target CHAR(36) NOT NULL, INDEX IDX_FE415D6CBA49495E (author_source), INDEX IDX_FE415D6CA3AC19D1 (author_target), PRIMARY KEY(author_source, author_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE job_author_current_optimize (process_all TINYINT(1) DEFAULT 0 NOT NULL, author_id VARCHAR(36) DEFAULT NULL, id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE author_is_current_author ADD CONSTRAINT FK_FE415D6CBA49495E FOREIGN KEY (author_source) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE author_is_current_author ADD CONSTRAINT FK_FE415D6CA3AC19D1 FOREIGN KEY (author_target) REFERENCES author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_author_current_optimize ADD CONSTRAINT FK_B33B2963BF396750 FOREIGN KEY (id) REFERENCES job (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE author_is_current_author DROP FOREIGN KEY FK_FE415D6CBA49495E');
        $this->addSql('ALTER TABLE author_is_current_author DROP FOREIGN KEY FK_FE415D6CA3AC19D1');
        $this->addSql('ALTER TABLE job_author_current_optimize DROP FOREIGN KEY FK_B33B2963BF396750');
        $this->addSql('DROP TABLE author_is_current_author');
        $this->addSql('DROP TABLE job_author_current_optimize');
    }
}
