<?php

declare(strict_types=1);

namespace AnzuSystems\AuthBundle\Exception;

use AnzuSystems\Contracts\Exception\AnzuException;

final class InvalidJwtException extends AnzuException
{
    public static function create(string $jwt): self
    {
        return new self(sprintf('Provided "%s" is not valid JWT token.', $jwt));
    }
}
