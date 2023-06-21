<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Cache\AssetFileCacheManager;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\MessageBusAwareTrait;
use App\Domain\Image\ImageRouteGenerator;
use App\HttpClient\NotificationClient;
use App\Messenger\Message\CdnPurgeMessage;
use App\Messenger\Message\ImageCachePurgeMessage;
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
