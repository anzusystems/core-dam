<?php

declare(strict_types=1);

namespace App\Domain\Asset;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Validator\Validator;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileManagerProvider;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileMessageDispatcher;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Exception\RuntimeException;
use AnzuSystems\CoreDamBundle\Traits\IndexManagerAwareTrait;
use App\Model\Dto\Asset\RtmpAssetDto;
use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemException;
use Throwable;

final class AssetRtmpFacade
{
    use IndexManagerAwareTrait;

    public function __construct(
        private readonly Validator $validator,
        private readonly AssetRtmpFactory $assetRtmpFactory,
        private readonly AssetFileManagerProvider $assetFileManagerProvider,
        private readonly AssetFileMessageDispatcher $assetFileMessageDispatcher,
    ) {
    }

    /**
     * @throws ValidationException
     * @throws NonUniqueResultException
     * @throws FilesystemException
     */
    public function createFromRtmp(RtmpAssetDto $dto): AssetFile
    {
        $this->validator->validate($dto);

        $assetFile = $this->assetRtmpFactory->createFromRtmp($dto);
        $manager = $this->assetFileManagerProvider->getManager($assetFile);

        try {
            $manager->beginTransaction();
            $manager->flush();
            $this->indexManager->index($assetFile->getAsset());
            $manager->commit();
        } catch (Throwable $exception) {
            $manager->rollback();

            throw new RuntimeException('asset_file_upload_failed', 0, $exception);
        }

        $this->assetFileMessageDispatcher->dispatchAssetFileChangeState($assetFile);

        return $assetFile;
    }
}
