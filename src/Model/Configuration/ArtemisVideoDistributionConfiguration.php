<?php

declare(strict_types=1);

namespace App\Model\Configuration;

use AnzuSystems\CoreDamBundle\Model\Configuration\TextsWriter\TextsWriterConfiguration;

final class ArtemisVideoDistributionConfiguration
{
    public const string DEFAULT_RUBRIC_ID = 'default_rubric_id';
    public const string CUSTOM_DATA_TO_DISTRIBUTION_MAP = 'custom_data_to_distribution_map';

    public function __construct(
        private readonly int $defaultRubricId,
        private readonly array $customDataToDistributionMap,
    ) {
    }

    public static function getFromArrayConfiguration(array $config): self
    {
        return new self(
            $config[self::DEFAULT_RUBRIC_ID] ?? 0,
            array_map(
                fn (array $episodeMapConfig): TextsWriterConfiguration => TextsWriterConfiguration::getFromArrayConfiguration($episodeMapConfig),
                $config[self::CUSTOM_DATA_TO_DISTRIBUTION_MAP] ?? []
            )
        );
    }

    public function getDefaultRubricId(): int
    {
        return $this->defaultRubricId;
    }

    /**
     * @return array<int, TextsWriterConfiguration>
     */
    public function getCustomDataToDistributionMap(): array
    {
        return $this->customDataToDistributionMap;
    }
}
