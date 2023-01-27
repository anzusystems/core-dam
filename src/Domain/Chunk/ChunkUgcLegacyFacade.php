<?php

declare(strict_types=1);

namespace App\Domain\Chunk;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\AssetFileCounter;
use AnzuSystems\CoreDamBundle\Domain\AssetFile\FileProcessor\MetadataProcessor;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkFactory;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkFileManager;
use AnzuSystems\CoreDamBundle\Domain\Chunk\ChunkManager;
use AnzuSystems\CoreDamBundle\Entity\AssetFile;
use AnzuSystems\CoreDamBundle\Entity\Chunk;
use AnzuSystems\CoreDamBundle\Model\Dto\Chunk\ChunkAdmCreateDto;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Throwable;

final readonly class ChunkUgcLegacyFacade
{
    public function __construct(
        private ChunkManager $chunkManager,
        private EntityValidator $entityValidator,
        private ChunkFactory $chunkFactory,
        private ChunkFileManager $chunkFileManager,
        private MetadataProcessor $metadataProcessor,
        private AssetFileCounter $assetFileCounter,
    ) {
    }

    /**
     * @throws ValidationException
     * @throws InvalidArgumentException
     */
    public function create(ChunkAdmCreateDto $createDto, AssetFile $assetFile): Chunk
    {
        $createDto->setAssetFile($assetFile);
        $this->entityValidator->validateDto($createDto);
        $chunk = $this->chunkFactory->createFromAdmDto($createDto);
        $this->chunkManager->setAssetFile($chunk, $assetFile);
        $this->chunkManager->setNotifyTo($assetFile);
        $uploadedFile = $createDto->getFile();

        $uploadedSize = (int) $createDto->getFile()->getSize();

        try {
            $this->chunkManager->beginTransaction();
            $this->chunkFileManager->saveChunk($chunk, $uploadedFile);
            $this->assetFileCounter->incrUploadedSize($assetFile, $uploadedSize);
            $this->chunkManager->create($chunk);

            if ($chunk->isFirstChunk()) {
                $this->metadataProcessor->process($assetFile, $createDto->getFile());
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
