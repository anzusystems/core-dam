<?php

declare(strict_types=1);

namespace App\Model\Configuration;

final class MediaApiSyncConfiguration
{
    public const STORAGE_NAME = 'storage_name';

    public function __construct(
        private readonly string $storageName,
    ) {
    }

    public static function getFromArrayConfiguration(array $config): self
    {
        return new self(
            $config[self::STORAGE_NAME] ?? '',
        );
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }
}
