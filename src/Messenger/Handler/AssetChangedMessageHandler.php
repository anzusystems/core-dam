<?php

declare(strict_types=1);

namespace App\Messenger\Handler;

use AnzuSystems\CoreDamBundle\Domain\ExtSystem\ExtSystemCallbackFacade;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use App\Messenger\Message\AssetChangedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AssetChangedMessageHandler
{
    public function __construct(
        private ExtSystemCallbackFacade $extSystemCallbackFacade,
        private AssetRepository $assetRepository,
    ) {
    }

    public function __invoke(AssetChangedMessage $message): void
    {
        $assets = $this->assetRepository->findByIds($message->getAssetIds());
        if ($assets->isEmpty()) {
            return;
        }

        $this->extSystemCallbackFacade->notifyAssetsChanged($assets);
    }
}
