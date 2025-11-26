<?php

declare(strict_types=1);

namespace App\Model\Configuration;

final class CmsAudioProcessedAutomatConfiguration
{
    public const string AUDIO_FREE_SLOT_NAME_KEY = 'audio_free_slot_name';
    public const string AUDIO_PREMIUM_SLOT_NAME_KEY = 'audio_premium_slot_name';
    public const string AUDIO_BONUS_SLOT_NAME_KEY = 'audio_bonus_slot_name';

    public function __construct(
        private readonly string $audioFreeSlotName,
        private readonly string $audioPremiumSlotName,
        private readonly string $audioBonusSlotName,
    ) {
    }

    public static function getFromArrayConfiguration(array $config): self
    {
        return new self(
            $config[self::AUDIO_FREE_SLOT_NAME_KEY] ?? '',
            $config[self::AUDIO_PREMIUM_SLOT_NAME_KEY] ?? '',
            $config[self::AUDIO_BONUS_SLOT_NAME_KEY] ?? '',
        );
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
}
