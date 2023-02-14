<?php

declare(strict_types=1);

namespace App\Domain\Chunk;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Traits\ValidatorAwareTrait;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileCounter;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\FileProcessor\MetadataProcessor;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkFactory;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkFileManager;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Chunk;
use AnzuSystems\CoreDamBundle\Model\Dto\Chunk\ChunkAdmCreateDto;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

final class ChunkUgcLegacyFacade
{
    use ValidatorAwareTrait;

    public function __construct(
        private readonly ChunkManager $chunkManager,
        private readonly ChunkFactory $chunkFactory,
        private readonly ChunkFileManager $chunkFileManager,
        private readonly MetadataProcessor $metadataProcessor,
        private readonly AssetFileCounter $assetFileCounter,
    ) {
    }

    /**
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function create(ChunkAdmCreateDto $createDto, AssetFile $assetFile): Chunk
    {
        $createDto->setAssetFile($assetFile);
        $this->validator->validate($createDto);
        $chunk = $this->chunkFactory->createFromAdmDto($createDto);
        $this->chunkManager->setAssetFile($chunk, $assetFile);
        $this->chunkManager->setNotifyTo($assetFile);
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $createDto->getFile();

        $uploadedSize = (int) $uploadedFile->getSize();

        try {
            $this->chunkManager->beginTransaction();
            $this->chunkFileManager->saveChunk($chunk, $uploadedFile);
            $this->assetFileCounter->incrUploadedSize($assetFile, $uploadedSize);
            $this->chunkManager->create($chunk);

            if ($chunk->isFirstChunk()) {
                $this->metadataProcessor->process($assetFile, $uploadedFile);
            }

            $this->chunkManager->commit();

            return $chunk;
        } catch (Throwable $exception) {
            $this->assetFileCounter->resetUploadedSize($assetFile);
            $this->chunkManager->rollback();

            throw new RuntimeException('chunk_create_failed', 0, $exception);
        }
    }
}
