<?php

declare(strict_types=1);

namespace App\Domain\ExtSystem;

/**
 * ExtSystem callback handler for the 'cms_tts' slug.
 *
 * Registered automatically via the #[AutoconfigureTag] on ExtSystemCallbackInterface
 * — no explicit DI config required.
 *
 * Phase 2: override notifyAssetsChanged() to build DamMediaSysDto with TTS fields
 * (extResourceName, extId, extVersion, voiceFamilySlug, lastRegeneratedAt,
 * includeInRecommendedPodcast, isGenerated). Currently delegates to parent —
 * sends standard AssetCmsSysDto payload without TTS-specific fields.
 */
final readonly class CmsTtsExtSystemCallback extends AbstractExtSystemCallback
{
    public static function getDefaultKeyName(): string
    {
        return self::CMS_TTS_SLUG;
    }
}
