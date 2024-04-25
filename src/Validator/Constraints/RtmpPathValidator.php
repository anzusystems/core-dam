<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\FileSystem\DamFileSystemProvider;
use League\Flysystem\FilesystemException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RtmpPathValidator extends ConstraintValidator
{
    public function __construct(
        private readonly DamFileSystemProvider $damFileSystemProvider,
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

        $fileSystem = $this->damFileSystemProvider->getRtmpFilesystem();
        if (false === $fileSystem->has($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
