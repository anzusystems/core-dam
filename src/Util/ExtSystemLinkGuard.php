<?php

declare(strict_types=1);

namespace App\Util;

use AnzuSystems\CoreDamBundle\Logger\DamLogger;
use App\DependencyInjection\Configuration;

final readonly class ExtSystemLinkGuard
{
    /**
     * @param array<string, bool> $extSystemConfiguration
     */
    public function __construct(
        private DamLogger $damLogger,
        private array $extSystemConfiguration,
    ) {
    }

    public function isCmsReadDisabled(): bool
    {
        if ($this->extSystemConfiguration[Configuration::CMS_READ_DISABLED] ?? Configuration::DEFAULT_DISABLED) {
            $this->damLogger->info('Configuration', 'CMS read disabled');

            return true;
        }

        return false;
    }

    public function isCmsWriteDisabled(): bool
    {
        if ($this->extSystemConfiguration[Configuration::CMS_WRITE_DISABLED] ?? Configuration::DEFAULT_DISABLED) {
            $this->damLogger->info('Configuration', 'CMS write disabled');

            return true;
        }

        return false;
    }

    public function isMediaApiWriteDisabled(): bool
    {
        if ($this->extSystemConfiguration[Configuration::MEDIA_API_WRITE_DISABLED] ?? Configuration::DEFAULT_DISABLED) {
            $this->damLogger->info('Configuration', 'Media API write disabled');

            return true;
        }

        return false;
    }
}
