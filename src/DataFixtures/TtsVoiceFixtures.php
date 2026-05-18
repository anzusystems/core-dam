<?php

declare(strict_types=1);

namespace App\DataFixtures;

use AnzuSystems\CommonBundle\DataFixtures\Fixtures\AbstractFixtures;
use AnzuSystems\CoreDamBundle\Entity\Voice;
use AnzuSystems\CoreDamBundle\Entity\VoiceFamily;
use AnzuSystems\CoreDamBundle\Model\Enum\TtsProvider;
use AnzuSystems\CoreDamBundle\Entity\ExtSystem;
use AnzuSystems\CoreDamBundle\Repository\ExtSystemRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Seeds TTS VoiceFamily + Voice entities for development/test environments.
 *
 * Requires:
 *   - ENV TTS_EXT_SYSTEM_SLUG (default: cms-tts) — the ExtSystem slug that owns the voice families.
 *   - ENV ELEVENLABS_DEFAULT_MALE_VOICE_ID  — ElevenLabs external voice ID for male voice.
 *   - ENV ELEVENLABS_DEFAULT_FEMALE_VOICE_ID — ElevenLabs external voice ID for female voice.
 *
 * If any required ENV is missing or the ExtSystem is not found, a WARNING is logged and the fixture
 * is silently skipped — never throws, as fixtures are convenience utilities.
 *
 * @extends AbstractFixtures<VoiceFamily>
 */
final class TtsVoiceFixtures extends AbstractFixtures
{
    public function __construct(
        private readonly ExtSystemRepository $extSystemRepository,
        private readonly string $ttsExtSystemSlug,
        private readonly string $defaultMaleVoiceId,
        private readonly string $defaultFemaleVoiceId,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getEnvironments(): array
    {
        return ['dev', 'test'];
    }

    public static function getIndexKey(): string
    {
        return VoiceFamily::class;
    }

    public function load(ProgressBar $progressBar): void
    {
        $extSystem = $this->extSystemRepository->findOneBy(['slug' => $this->ttsExtSystemSlug]);
        if (null === $extSystem) {
            $this->logger->warning(
                'TtsVoiceFixtures: ExtSystem not found, skipping.',
                ['slug' => $this->ttsExtSystemSlug],
            );

            return;
        }

        foreach ($progressBar->iterate($this->getData($extSystem)) as $voiceFamily) {
            $this->entityManager->persist($voiceFamily);
            $this->addToRegistry($voiceFamily, $voiceFamily->getSlug());
        }

        $this->entityManager->flush();
    }

    /**
     * @return iterable<VoiceFamily>
     */
    private function getData(ExtSystem $extSystem): iterable
    {
        if ('' !== $this->defaultMaleVoiceId) {
            $maleFamily = (new VoiceFamily())
                ->setExtSystem($extSystem)
                ->setSlug('sme_default_male')
                ->setDisplayName('SME Default Male')
                ->setLanguage('sk')
                ->setPreferredProvider(TtsProvider::Elevenlabs)
                ->setActive(true)
            ;

            $maleVoice = (new Voice())
                ->setVoiceFamily($maleFamily)
                ->setProvider(TtsProvider::Elevenlabs)
                ->setExternalVoiceId($this->defaultMaleVoiceId)
                ->setMetadata([])
                ->setActive(true)
            ;
            $maleFamily->getVoices()->add($maleVoice);

            yield $maleFamily;
        } else {
            $this->logger->warning(
                'TtsVoiceFixtures: ELEVENLABS_DEFAULT_MALE_VOICE_ID is empty, skipping male voice family.'
            );
        }

        if ('' !== $this->defaultFemaleVoiceId) {
            $femaleFamily = (new VoiceFamily())
                ->setExtSystem($extSystem)
                ->setSlug('sme_default_female')
                ->setDisplayName('SME Default Female')
                ->setLanguage('sk')
                ->setPreferredProvider(TtsProvider::Elevenlabs)
                ->setActive(true)
            ;

            $femaleVoice = (new Voice())
                ->setVoiceFamily($femaleFamily)
                ->setProvider(TtsProvider::Elevenlabs)
                ->setExternalVoiceId($this->defaultFemaleVoiceId)
                ->setMetadata([])
                ->setActive(true)
            ;
            $femaleFamily->getVoices()->add($femaleVoice);

            yield $femaleFamily;
        } else {
            $this->logger->warning(
                'TtsVoiceFixtures: ELEVENLABS_DEFAULT_FEMALE_VOICE_ID is empty, skipping female voice family.'
            );
        }
    }
}
