<?php

declare(strict_types=1);


namespace App\DamMigrations;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastImportMode;
use AnzuSystems\CoreDamBundle\Model\Enum\PodcastLastImportStatus;
use App\App;
use App\Entity\User;
use App\Model\MigrateConfig;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Result;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Uid\Uuid;

final class LegacyDamPodcastEpisodesMigrations extends AbstractMigrations
{
    use OutputUtilTrait;

    private const ARTEMIS_SHAPE = [
        'id_media_channel' => 'integer',
        'id_section' => 'integer',
        'id_media_channel_status' => 'string',
        'id_media_service' => 'string',
        'title' => 'string',
        'description' => 'string',
        'id_image' => 'integer',
        'rss_feed' => 'string',
    ];
    private const LEGACY_DAM_SHAPE = [
        'id' => 'integer',
        'created_by_id' => 'integer',
        'modified_by_id' => 'integer',
        'title' => 'string',
        'created_at' => 'string',
        'modified_at' => 'string',
        'type' => 'string',
    ];


    public function migrate(MigrateConfig $migrateConfig): void
    {
//        dd($this->getEpisodes()[0]);
    }
//
//    private function aa(): array
//    {
//        $sql = '
//            SELECT * FROM
//        ';
//
//        return $this->damLegacyConnection->fetchAllAssociative(
//
//        );
//    }
//
//    private function getEpisodes(): array
//    {
//        $sql = '
//            SELECT * FROM artemis_media
//        ';
//
//        return $this->artemisConnection->fetchAllAssociative(
//            $sql
//        );
//    }
}
