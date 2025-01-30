<?php

declare(strict_types=1);

namespace App\Model\Domain\Distribution;

interface DistributionPubDecoratorInterface
{
    public function getId(): string;
    public function getType(): string;
}
