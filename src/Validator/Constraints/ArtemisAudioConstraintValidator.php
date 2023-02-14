<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use AnzuSystems\CoreDamBundle\Repository\PodcastEpisodeRepository;
use App\Entity\ArtemisAudioDistribution;
use App\Exception\ValidationException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ArtemisAudioConstraintValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PodcastEpisodeRepository $episodeRepository,
    ) {
    }

    /**
     * @param ArtemisAudioDistribution $value
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (false === ($value instanceof ArtemisAudioDistribution)) {
            throw new UnexpectedTypeException($constraint, ArtemisAudioDistribution::class);
        }

        $episodeId = $value->getTexts()->getEpisodeId();

        if (empty($episodeId)) {
            return;
        }

        $podcastEpisode = $this->episodeRepository->find($episodeId);
        if (false === ($podcastEpisode?->getAsset()?->getId() === $value->getAssetId())) {
            $this->context
                ->buildViolation(ValidationException::ERROR_FIELD_INVALID)
                ->atPath('texts.episodeId')
                ->addViolation();
        }
    }
}
