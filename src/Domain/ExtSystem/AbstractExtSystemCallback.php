<?php

declare(strict_types=1);

namespace App\Domain\ExtSystem;

use AnzuSystems\CoreDamBundle\Domain\ExtSystem\ExtSystemCallbackInterface;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Entity\JobImageCopy;
use AnzuSystems\CoreDamBundle\Model\Dto\Job\JobImageCopyResultDto;
use AnzuSystems\CoreDamBundle\Model\Dto\Job\JobImageCopyResultItemDto;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use App\HttpClient\CmsClient;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Uuid;

abstract readonly class AbstractExtSystemCallback implements ExtSystemCallbackInterface
{
    public const string CMS_SLUG = 'cms';
    public const string DAILY_SPORT_SLUG = 'dennik_sport';

    public function __construct(
        private AssetRepository $assetRepository,
        private CmsClient $cmsClient,
    ) {
    }

    public function notifyFinishedJobImageCopy(JobImageCopy $jobImageCopy): void
    {
        $this->cmsClient->notifyFinishedJobImageCopy(
            $this->createJobImageCopyResult($jobImageCopy)
        );
    }

    protected function createJobImageCopyResult(JobImageCopy $jobImageCopy): JobImageCopyResultDto
    {
        /** @var array<array-key, JobImageCopyResultItemDto> $items */
        $items = [];

        foreach ($jobImageCopy->getItems() as $item) {
            $targetAsset = is_string($item->getTargetAssetId()) ? $this->assetRepository->find($item->getTargetAssetId()) : null;
            $targetMainFile = $targetAsset?->getMainFile();

            $sourceAsset = $this->assetRepository->find($item->getSourceAssetId());
            $sourceMainFile = $sourceAsset?->getMainFile();

            if ($sourceMainFile instanceof ImageFile &&
                $targetMainFile instanceof ImageFile &&
                $targetMainFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)
            ) {
                $items[] = (new JobImageCopyResultItemDto())
                    ->setSourceImageId(Uuid::fromString((string) $sourceMainFile->getId()))
                    ->setTargetImageId(Uuid::fromString((string) $targetMainFile->getId()))
                ;
            }
        }

        return (new JobImageCopyResultDto())
            ->setStatus($jobImageCopy->getStatus())
            ->setJobImageCopy($jobImageCopy)
            ->setTargetAssetLicence($jobImageCopy->getLicence())
            ->setFailedCount($jobImageCopy->getItems()->count() - count($items))
            ->setItems(new ArrayCollection($items))
        ;
    }
}
