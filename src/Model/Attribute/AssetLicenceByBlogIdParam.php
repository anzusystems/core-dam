<?php

declare(strict_types=1);

namespace App\Model\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class AssetLicenceByBlogIdParam
{
    public function __construct(
        public string $name,
    ) {
    }
}
