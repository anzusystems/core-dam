<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Messenger\Message\RtmpImportMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RtmpImportMessageHandler
{
    use MessageBusAwareTrait;

    public function __construct(
    ) {
    }

    public function __invoke(RtmpImportMessage $message): void
    {
    }
}
