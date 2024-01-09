<?php

namespace App\Model;

use Symfony\Component\Console\Input\InputInterface;

final class StaticFilesMigrateConfig
{
    public const DROP_OPTION = 'drop';
    public const MIGRATE_OPTION = 'migrate';
    public const LIMIT_OPTION = 'limit';
    public const EXTENSION_OPTION = 'extension';
    public const STATUS_OPTION = 'status';

    public function __construct(
        private readonly bool $drop = false,
        private readonly bool $migrate = true,
        private readonly ?int $limit = null,
        private readonly ?string $extension = null,
        private readonly string $status = 'waiting',
        private string $fileName = 'document_migrate_data.csv',
    ) {
    }

    public static function createFromInput(InputInterface $input): self
    {
        return new self(
            drop: self::getBooleanOption($input, self::DROP_OPTION),
            migrate: self::getBooleanOption($input, self::MIGRATE_OPTION, true),
            limit: self::getIntegerOption($input, self::LIMIT_OPTION),
            extension: self::getStringOption($input, self::EXTENSION_OPTION),
            status: self::getStringOption($input, self::STATUS_OPTION, 'waiting'),
        );
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function isDrop(): bool
    {
        return $this->drop;
    }

    public function isMigrate(): bool
    {
        return $this->migrate;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getStatus(): string
    {
        return $this->status;
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

    private static function getIntegerOption(
        InputInterface $input,
        string $optionName,
        ?int $default = null,
    ): ?int {
        $value = $input->getOption($optionName);
        if (is_numeric($value)) {
            return (int) $value;
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
}
