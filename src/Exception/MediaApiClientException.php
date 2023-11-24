<?php

declare(strict_types=1);

namespace App\Exception;

use AnzuSystems\Contracts\Exception\AnzuException;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use Throwable;

final class MediaApiClientException extends AnzuException
{
    public static function create(string $message, ?Throwable $previous = null): self
    {
        return new self(sprintf('Mediaapi client exception with message (%s)', $message), 0, $previous);
    }
}
