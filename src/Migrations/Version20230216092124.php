<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230216092124 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDA13A78ED');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDA13A78ED FOREIGN KEY (image_preview_id) REFERENCES image_preview (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD0A13A78ED');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD0A13A78ED FOREIGN KEY (image_preview_id) REFERENCES image_preview (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE video_file DROP FOREIGN KEY FK_8B086BCCA13A78ED');
        $this->addSql('ALTER TABLE video_file ADD CONSTRAINT FK_8B086BCCA13A78ED FOREIGN KEY (image_preview_id) REFERENCES image_preview (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE podcast DROP FOREIGN KEY FK_D7E805BDA13A78ED');
        $this->addSql('ALTER TABLE podcast ADD CONSTRAINT FK_D7E805BDA13A78ED FOREIGN KEY (image_preview_id) REFERENCES image_preview (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE podcast_episode DROP FOREIGN KEY FK_77EB2BD0A13A78ED');
        $this->addSql('ALTER TABLE podcast_episode ADD CONSTRAINT FK_77EB2BD0A13A78ED FOREIGN KEY (image_preview_id) REFERENCES image_preview (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE video_file DROP FOREIGN KEY FK_8B086BCCA13A78ED');
        $this->addSql('ALTER TABLE video_file ADD CONSTRAINT FK_8B086BCCA13A78ED FOREIGN KEY (image_preview_id) REFERENCES image_preview (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
