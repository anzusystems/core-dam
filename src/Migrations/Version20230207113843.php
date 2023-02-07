<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230207113843 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE video_show (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', licence_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', texts_title VARCHAR(128) NOT NULL, INDEX IDX_359984DD26EF07C9 (licence_id), INDEX IDX_359984DDB03A8386 (created_by_id), INDEX IDX_359984DD99049ECE (modified_by_id), INDEX IDX_name (texts_title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video_show_episode (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', video_show_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', asset_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', created_by_id INT DEFAULT NULL, modified_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', position SMALLINT NOT NULL, texts_title VARCHAR(255) NOT NULL, INDEX IDX_1B0B20F6352C15C9 (video_show_id), INDEX IDX_1B0B20F65DA1941 (asset_id), INDEX IDX_1B0B20F6B03A8386 (created_by_id), INDEX IDX_1B0B20F699049ECE (modified_by_id), INDEX IDX_video_show_position (video_show_id, position), INDEX IDX_position (position), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE video_show ADD CONSTRAINT FK_359984DD26EF07C9 FOREIGN KEY (licence_id) REFERENCES asset_licence (id)');
        $this->addSql('ALTER TABLE video_show ADD CONSTRAINT FK_359984DDB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE video_show ADD CONSTRAINT FK_359984DD99049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE video_show_episode ADD CONSTRAINT FK_1B0B20F6352C15C9 FOREIGN KEY (video_show_id) REFERENCES video_show (id)');
        $this->addSql('ALTER TABLE video_show_episode ADD CONSTRAINT FK_1B0B20F65DA1941 FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE video_show_episode ADD CONSTRAINT FK_1B0B20F6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE video_show_episode ADD CONSTRAINT FK_1B0B20F699049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX IDX_title_licence_id ON podcast_episode');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE video_show DROP FOREIGN KEY FK_359984DD26EF07C9');
        $this->addSql('ALTER TABLE video_show DROP FOREIGN KEY FK_359984DDB03A8386');
        $this->addSql('ALTER TABLE video_show DROP FOREIGN KEY FK_359984DD99049ECE');
        $this->addSql('ALTER TABLE video_show_episode DROP FOREIGN KEY FK_1B0B20F6352C15C9');
        $this->addSql('ALTER TABLE video_show_episode DROP FOREIGN KEY FK_1B0B20F65DA1941');
        $this->addSql('ALTER TABLE video_show_episode DROP FOREIGN KEY FK_1B0B20F6B03A8386');
        $this->addSql('ALTER TABLE video_show_episode DROP FOREIGN KEY FK_1B0B20F699049ECE');
        $this->addSql('DROP TABLE video_show');
        $this->addSql('DROP TABLE video_show_episode');
        $this->addSql('CREATE INDEX IDX_title_licence_id ON podcast_episode (texts_title)');
    }
}
