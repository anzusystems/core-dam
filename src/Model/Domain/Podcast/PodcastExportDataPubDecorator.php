<?php

declare(strict_types=1);

namespace App\Model\Domain\Podcast;

use AnzuSystems\CoreDamBundle\Entity\PodcastExportData;
use AnzuSystems\CoreDamBundle\Model\Enum\DeviceType;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use AnzuSystems\SerializerBundle\Handler\Handlers\EntityIdHandler;

final class PodcastExportDataPubDecorator
{
    private PodcastExportData $podcastExportData;

    public static function getInstance(PodcastExportData $podcastExportData): self
    {
        return new self()
            ->setPodcastExportData($podcastExportData)
        ;
    }

    #[Serialize(serializedName: 'id', handler: EntityIdHandler::class)]
    public function getExportData(): PodcastExportData
    {
        return $this->podcastExportData;
    }

    public function setPodcastExportData(PodcastExportData $podcastExportData): self
    {
        $this->podcastExportData = $podcastExportData;
        return $this;
    }

    #[Serialize]
    public function getDeviceType(): DeviceType
    {
        return $this->podcastExportData->getDeviceType();
    }

    #[Serialize(strategy: Serialize::KEYS_VALUES)]
    public function getBody(): array
    {
        return $this->podcastExportData->getBody();
    }
}
