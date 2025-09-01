<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Console\Input\InputInterface;

final readonly class MediaApiMigrateConfig
{
    public const string FROM_ID_ARG = 'fromId';
    public const string LIMIT_OPT = 'limit';
    public const string TO_ID_ARG = 'toId';
    public const string DROP_MIGRATION_TABLE_OPTION = 'dropMigrationTable';
    public const string STAGES_OPT = 'stages';
    public const string MEDIA_API_SOURCE_STORAGE_OPT = 'storage';
    public const int MAX_INT_SIZE = 4_294_967_295;

    public const string STAGE_BUILD_TABLE = 'buildTable';
    public const string STAGE_MIGRATE_IMAGES = 'migrateImages';
    public const string STAGE_MIGRATE_USERS = 'migrateUsers';
    public const string STAGE_IMAGE_POSTPROCESS = 'imagePostprocess';

    public const string REMOTE_STORAGE = 'cms.media_api.prod_storage';
    public const string LOCAL_STORAGE = 'cms.media_api.local_storage';

    public const array DEFAULT_STAGES = [
        self::STAGE_BUILD_TABLE,
        self::STAGE_MIGRATE_IMAGES,
        self::STAGE_MIGRATE_USERS,
        self::STAGE_IMAGE_POSTPROCESS,
    ];

    public function __construct(
        private ?int $fromId,
        private ?int $toId,
        private bool $dropMigrationTable,
        private int $batchSize = 40,
        private array $stages = [],
        private ?int $limit = null,
        private string $status = 'waiting',
        private string $mediaApiSourceStorage = 'cms.media_api.prod_storage',
    ) {
    }

    public static function createFromInput(InputInterface $input): self
    {
        return new self(
            fromId: self::getNumericArgument($input, self::FROM_ID_ARG),
            toId: self::getNumericArgument($input, self::TO_ID_ARG),
            dropMigrationTable: self::getBooleanOption($input, self::DROP_MIGRATION_TABLE_OPTION),
            stages: self::getArrayOption($input, self::STAGES_OPT),
            limit: self::getNumericArgument($input, self::LIMIT_OPT, false),
            mediaApiSourceStorage: self::getStringOpt($input, self::MEDIA_API_SOURCE_STORAGE_OPT)
        );
    }

    public function getMediaApiSourceStorage(): string
    {
        return $this->mediaApiSourceStorage;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function allowImagePostprocess(): bool
    {
        return in_array(self::STAGE_IMAGE_POSTPROCESS, $this->stages, true);
    }

    public function allowBuildTableStage(): bool
    {
        return in_array(self::STAGE_BUILD_TABLE, $this->stages, true);
    }

    public function allowMigrateImagesStage(): bool
    {
        return in_array(self::STAGE_MIGRATE_IMAGES, $this->stages, true);
    }

    public function allowMigrateUsersStage(): bool
    {
        return in_array(self::STAGE_MIGRATE_USERS, $this->stages, true);
    }

    public function getStages(): array
    {
        return $this->stages;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function isDropMigrationTable(): bool
    {
        return $this->dropMigrationTable;
    }

    public function getFromId(): ?int
    {
        return $this->fromId;
    }

    public function getToId(): ?int
    {
        return $this->toId;
    }

    private static function getNumericArgument(InputInterface $input, string $argName, bool $isArgument = true): ?int
    {
        $value = $isArgument ? $input->getArgument($argName) : $input->getOption($argName);
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private static function getStringOpt(InputInterface $input, string $optionName): string
    {
        $value = $input->getOption($optionName);
        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    private static function getBooleanOption(InputInterface $input, string $optionName): bool
    {
        $value = $input->getOption($optionName);
        if (is_bool($value)) {
            return $value;
        }

        return false;
    }

    private static function getArrayOption(InputInterface $input, string $optionName): array
    {
        $value = $input->getOption($optionName);
        if (is_array($value)) {
            return $value;
        }

        return [];
    }
}
