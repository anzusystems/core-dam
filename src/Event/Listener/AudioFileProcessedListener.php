<?php

declare(strict_types=1);

namespace App\Event\Listener;

use AnzuSystems\CoreDamBundle\Entity\AudioFile;
use AnzuSystems\CoreDamBundle\Event\AssetFileChangeStateEvent;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetFileProcessStatus;
use AnzuSystems\CoreDamBundle\Repository\AudioFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Domain\Audio\AudioFileOnProcessedAutomat;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: AssetFileChangeStateEvent::class)]
final readonly class AudioFileProcessedListener
{
    public function __construct(
        private AudioFileOnProcessedAutomat $automat,
        private AudioFileRepository $audioFileRepository,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws NonUniqueResultException
     */
    public function __invoke(AssetFileChangeStateEvent $event): void
    {
        $assetFile = $event->getAsset();
        if (
            $assetFile instanceof AudioFile &&
            $assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Processed)
        ) {
            $this->automat->makeAudioPublicUrl($assetFile);
            $this->automat->activateForPublicExportAndPublishPremium($assetFile);
        }

        if (
            $assetFile instanceof AudioFile &&
            false === empty($assetFile->getAssetAttributes()->getOriginAssetId()) &&
            $assetFile->getAssetAttributes()->getStatus()->is(AssetFileProcessStatus::Duplicate)
        ) {
            $originAudioFile = $this->audioFileRepository->find($assetFile->getAssetAttributes()->getOriginAssetId());
            if (null === $originAudioFile) {
                return;
            }

            if ($originAudioFile instanceof AudioFile) {
                $this->automat->makeAudioPublicUrl($originAudioFile);
                $this->automat->activateForPublicExportAndPublishPremium($originAudioFile);
            }
        }
    }
}
