<?php

declare(strict_types=1);

namespace App\FileSystem;

use AnzuSystems\CoreDamBundle\Exception\InvalidArgumentException;
use AnzuSystems\CoreDamBundle\FileSystem\AbstractFilesystem;
use AnzuSystems\CoreDamBundle\FileSystem\StorageProviderContainer;

final class DamFileSystemProvider
{
    private const RTMP_FILE_SYSTEM_NAME = 'cms.rtmp';
    public function __construct(
        private readonly StorageProviderContainer $storageProviderContainer,
    ) {
    }

    public function getRtmpFilesystem(): AbstractFilesystem
    {
        if ($this->storageProviderContainer->has(self::RTMP_FILE_SYSTEM_NAME)) {
            return $this->storageProviderContainer->get(self::RTMP_FILE_SYSTEM_NAME);
        }

        throw new InvalidArgumentException('Undefined public storage');
    }
}
