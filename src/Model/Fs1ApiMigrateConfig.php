<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Console\Input\InputInterface;

final readonly class Fs1ApiMigrateConfig
{
    public const string DROP_OPTION = 'drop';
    public const string MIGRATE_OPTION = 'migrate';
    public const string CLEAN_OPTION = 'clean';
    public const string STATUS_OPTION = 'status';
    public const string JW_DISTRIBUTE_OPTION = 'jw';
    public const string SHOW_ID_OPTION = 'show';

    public const string LIMIT_OPTION = 'limit';

    public function __construct(
        private bool $drop = false,
        private bool $migrate = true,
        private bool $clean = false,
        private bool $jwDistribute = false,
        private string $status = 'waiting',
        private string $fileName = 'document_migrate_data.csv',
        private string $storageName = 'cms.video',
        private string $showId = '',
        private ?int $limit = null,
    ) {
    }

    public static function createFromInput(InputInterface $input): self
    {
        return new self(
            drop: self::getBooleanOption($input, self::DROP_OPTION),
            migrate: self::getBooleanOption($input, self::MIGRATE_OPTION, true),
            clean: self::getBooleanOption($input, self::CLEAN_OPTION),
            jwDistribute: self::getBooleanOption($input, self::JW_DISTRIBUTE_OPTION),
            status: self::getStringOption($input, self::STATUS_OPTION, 'waiting'),
            showId: self::getStringOption($input, self::SHOW_ID_OPTION),
            limit: self::getIntOption($input, self::LIMIT_OPTION),
        );
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function isDrop(): bool
    {
        return $this->drop;
    }

    public function isMigrate(): bool
    {
        return $this->migrate;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function isClean(): bool
    {
        return $this->clean;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function isJwDistribute(): bool
    {
        return $this->jwDistribute;
    }

    public function getShowId(): string
    {
        return $this->showId;
    }

    private static function getBooleanOption(
        InputInterface $input,
        string $optionName,
        bool $default = false,
    ): bool {
        $value = $input->getOption($optionName);
        if (is_bool($value)) {
            return $value;
        }

        return $default;
    }

    private static function getStringOption(
        InputInterface $input,
        string $optionName,
        string $default = '',
    ): string {
        $value = $input->getOption($optionName);
        if (is_string($value)) {
            return $value;
        }

        return $default;
    }

    private static function getIntOption(
        InputInterface $input,
        string $optionName,
    ): ?int {
        $value = $input->getOption($optionName);
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
