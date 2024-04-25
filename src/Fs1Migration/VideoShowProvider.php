<?php

declare(strict_types=1);

namespace App\Fs1Migration;

use AnzuSystems\CoreDamBundle\Command\Traits\OutputUtilTrait;
use App\Entity\User;
use App\MediaApiMigrations\ConnectionDecorator;
use App\Model\Csv\Fs1CsvFile;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\String\ByteString;
use Symfony\Component\Uid\Uuid;

final class VideoShowProvider
{
    use OutputUtilTrait;
    private const LICENCE_ID = 100_000;

    /**
     * @var array<string, string>
     */
    private array $showCache = [];

    /**
     * @var array<string, string>
     */
    private array $episodeCache = [];
    private ConnectionDecorator $connectionDecorator;

    public function __construct(
        private readonly Connection $defaultConnection,
    ) {
        $this->connectionDecorator = new ConnectionDecorator($this->defaultConnection);
    }

    /**
     * @throws Exception
     */
    public function getEpisode(Fs1CsvFile $file, string $videoTitle): string
    {
        $showId = $this->getShow($file);
        if (empty($showId)) {
            return '';
        }

        if (empty($videoTitle)) {
            return '';
        }

        $episodeTitle = sprintf(
            '%s#%d',
            $videoTitle,
            $file->getId()
        );

        if (isset($this->episodeCache[$episodeTitle])) {
            return $this->showCache[$episodeTitle];
        }

        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM video_show_episode WHERE texts_title = :title',
            [
                'title' => $episodeTitle,
            ]
        );

        if (false === is_string($id)) {
            return $this->createEpisode($file, $showId, $episodeTitle);
        }

        $this->showCache[$episodeTitle] = $id;

        return $id;
    }

    /**
     * @throws Exception
     */
    public function getShow(Fs1CsvFile $file): string
    {
        if ('NULL' === $file->getSeriesName()) {
            return '';
        }

        $title = sprintf(
            '%s#%s',
            (new ByteString($file->getSeriesName()))->toUnicodeString()->toString(),
            $file->getSeriesId()
        );

        if (isset($this->showCache[$title])) {
            return $this->showCache[$title];
        }

        $id = $this->defaultConnection->fetchOne(
            'SELECT id FROM video_show WHERE texts_title = :title',
            [
                'title' => $title,
            ]
        );

        if (false === is_string($id)) {
            return $this->createShow($file, $title);
        }

        $this->showCache[$title] = $id;

        return $id;
    }

    /**
     * @throws Exception
     */
    private function createShow(Fs1CsvFile $file, string $title): string
    {
        $id = (string) Uuid::v6();

        $this->connectionDecorator->prepareBulkInsert(
            'video_show',
            [
                'id' => $id,
                'texts_title' => $title,
                'licence_id' => self::LICENCE_ID,
                'created_at' => $file->getCreatedAt()->format(DateTimeInterface::ATOM),
                'modified_at' => $file->getCreatedAt()->format(DateTimeInterface::ATOM),
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
            ]
        );
        $this->connectionDecorator->flush();
        $this->showCache[$title] = $id;

        return $id;
    }

    /**
     * @throws Exception
     */
    private function createEpisode(Fs1CsvFile $file, string $showId, string $title): string
    {
        $id = (string) Uuid::v6();

        $this->connectionDecorator->prepareBulkInsert(
            'video_show_episode',
            [
                'id' => $id,
                'video_show_id' => $showId,
                'texts_title' => $title,
                'created_at' => $file->getCreatedAt()->format(DateTimeInterface::ATOM),
                'modified_at' => $file->getCreatedAt()->format(DateTimeInterface::ATOM),
                'created_by_id' => User::ID_CONSOLE,
                'modified_by_id' => User::ID_CONSOLE,
                'position' => 0,
            ]
        );
        $this->connectionDecorator->flush();
        $this->episodeCache[$title] = $id;

        return $id;
    }
}
