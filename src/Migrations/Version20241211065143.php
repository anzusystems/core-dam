<?php

declare(strict_types=1);

namespace App\Migrations;

use App\App;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241211065143 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $idConsole = App::getUserIdConsole();
        //        $this->addSql("
        //            INSERT INTO author_clean_phrase(phrase, mode, type, author_replacement_id, ext_system_id, created_at,
        //                                            modified_at, created_by_id, modified_by_id, flags_word_boundary, position)
        //            VALUES  ('/', 'split', 'word', null, 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                (',', 'split', 'word', null, 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                (';', 'split', 'word', null, 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('©', 'remove', 'word', null, 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('TASR -', 'replace', 'word', '1ee8d03e-a671-628e-8773-3162c5e36bbd', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('TASR –', 'replace', 'word', '1ee8d03e-a671-628e-8773-3162c5e36bbd', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('– TASR', 'replace', 'word', '1ee8d03e-a671-628e-8773-3162c5e36bbd', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('- TASR', 'replace', 'word', '1ee8d03e-a671-628e-8773-3162c5e36bbd', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('- AP', 'replace', 'word', '1efa35fa-c24c-68d6-b4f0-db55f0ec0fbf', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('– AP', 'replace', 'word', '1efa35fa-c24c-68d6-b4f0-db55f0ec0fbf', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('AP –', 'replace', 'word', '1efa35fa-c24c-68d6-b4f0-db55f0ec0fbf', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('AP -', 'replace', 'word', '1efa35fa-c24c-68d6-b4f0-db55f0ec0fbf', 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100),
        //                ('©', 'remove', 'word', null, 1, NOW(), NOW(), {$idConsole}, {$idConsole}, 0, 100)
        //        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM author_clean_phrase where true');
    }
}
