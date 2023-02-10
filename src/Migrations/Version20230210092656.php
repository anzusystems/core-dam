<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230210092656 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, status VARCHAR(255) NOT NULL, started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', finished_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_batch_processed_record VARCHAR(255) NOT NULL, batch_processed_iteration_count INT NOT NULL, result VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', dtype VARCHAR(255) NOT NULL, target_user_id INT DEFAULT NULL, anonymize_user TINYINT(1) DEFAULT NULL, INDEX IDX_FBD8E0F8B03A8386 (created_by_id), INDEX IDX_FBD8E0F899049ECE (modified_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F899049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8B03A8386');
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F899049ECE');
        $this->addSql('DROP TABLE job');
    }
}
