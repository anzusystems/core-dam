<?php

declare(strict_types=1);

namespace App\Model\Configuration;

use AnzuSystems\CoreDamBundle\Model\Configuration\TextsWriter\TextsWriterConfiguration;

final class ArtemisAudioDistributionConfiguration
{
    public const AUDIO_FREE_SLOT_NAME_KEY = 'audio_free_slot_name';
    public const AUDIO_PREMIUM_SLOT_NAME_KEY = 'audio_premium_slot_name';
    public const AUDIO_BONUS_SLOT_NAME_KEY = 'audio_bonus_slot_name';
    public const DEFAULT_RUBRIC_ID = 'default_rubric_id';
    public const CUSTOM_DATA_TO_DISTRIBUTION_MAP = 'custom_data_to_distribution_map';
    public const RSS_JW_DISTRIBUTE = 'rss_jw_distribute';

    public function __construct(
        private readonly string $audioFreeSlotName,
        private readonly string $audioPremiumSlotName,
        private readonly string $audioBonusSlotName,
        private readonly int $defaultRubricId,
        private readonly array $customDataToDistributionMap,
        private readonly bool $rssJwDistribute,
    ) {
    }

    public static function getFromArrayConfiguration(array $config): self
    {
        return new self(
            $config[self::AUDIO_FREE_SLOT_NAME_KEY] ?? '',
            $config[self::AUDIO_PREMIUM_SLOT_NAME_KEY] ?? '',
            $config[self::AUDIO_BONUS_SLOT_NAME_KEY] ?? '',
            $config[self::DEFAULT_RUBRIC_ID] ?? 0,
            array_map(
                fn (array $episodeMapConfig): TextsWriterConfiguration => TextsWriterConfiguration::getFromArrayConfiguration($episodeMapConfig),
                $config[self::CUSTOM_DATA_TO_DISTRIBUTION_MAP] ?? []
            ),
            $config[self::RSS_JW_DISTRIBUTE] ?? false
        );
    }

    public function getDefaultRubricId(): int
    {
        return $this->defaultRubricId;
    }

    public function getAudioFreeSlotName(): string
    {
        return $this->audioFreeSlotName;
    }

    public function getAudioPremiumSlotName(): string
    {
        return $this->audioPremiumSlotName;
    }

    public function getAudioBonusSlotName(): string
    {
        return $this->audioBonusSlotName;
    }

    public function isRssJwDistribute(): bool
    {
        return $this->rssJwDistribute;
    }

    /**
     * @return array<int, TextsWriterConfiguration>
     */
    public function getCustomDataToDistributionMap(): array
    {
        return $this->customDataToDistributionMap;
    }
}
