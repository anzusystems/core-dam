<?php

declare(strict_types=1);

namespace App\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class SerializeIterableParam
{
    public function __construct(
        public string $type,
    ) {
    }
}
