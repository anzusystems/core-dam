<?php

declare(strict_types=1);

namespace App\Model\Domain\Distribution;

use AnzuSystems\CoreDamBundle\Entity\YoutubeDistribution;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class YoutubeDistributionPubDecorator implements DistributionPubDecoratorInterface
{
    private const string TYPE = 'youtube';
    private const string FALLBACK_TEMPLATE = 'https://www.youtube.com/watch?v=%s';

    private YoutubeDistribution $distribution;

    public static function getInstance(YoutubeDistribution $distribution): self
    {
        return (new self())
            ->setDistribution($distribution)
        ;
    }

    public function getDistribution(): YoutubeDistribution
    {
        return $this->distribution;
    }

    public function setDistribution(YoutubeDistribution $distribution): self
    {
        $this->distribution = $distribution;
        return $this;
    }

    #[Serialize]
    public function getId(): string
    {
        return $this->distribution->getExtId();
    }

    #[Serialize]
    public function getType(): string
    {
        return self::TYPE;
    }

    #[Serialize]
    public function getFallbackUrl(): string
    {
        if (StringHelper::isNotEmpty($this->getId())) {
            return sprintf(self::FALLBACK_TEMPLATE, $this->getId());
        }

        return '';
    }
}
