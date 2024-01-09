<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use AnzuSystems\CoreDamBundle\Exception\DomainException;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use App\Configuration\ConfigurationProvider;
use League\Flysystem\FilesystemException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MediaApiPathValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ConfigurationProvider $damConfigurationProvider,
        private readonly FileSystemProvider $fileSystemProvider,
    ) {
    }

    /**
     * @param RtmpPath $constraint
     * @throws FilesystemException
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (false === is_string($value)) {
            throw new UnexpectedTypeException($constraint, 'string');
        }

        if (empty($value)) {
            return;
        }

        $storageName = $this->damConfigurationProvider->getMediaApiSyncConfiguration()->getStorageName();
        $fileSystem = $this->fileSystemProvider->getFileSystemByStorageName($storageName);

        if (null === $fileSystem) {
            throw new DomainException('Filesystem not found');
        }

        if (false === $fileSystem->has($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
