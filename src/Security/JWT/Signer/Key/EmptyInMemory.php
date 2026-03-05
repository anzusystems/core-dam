<?php

declare(strict_types=1);

namespace App\Security\JWT\Signer\Key;

use App\App;
use Lcobucci\JWT\Signer\Key;

final readonly class EmptyInMemory implements Key
{
    public function contents(): string
    {
        /** @phpstan-ignore return.type */
        return App::EMPTY_STRING;
    }

    public function passphrase(): string
    {
        return App::EMPTY_STRING;
    }
}
