<?php

declare(strict_types=1);

namespace App\Model\Domain\Distribution;

use AnzuSystems\CoreDamBundle\Entity\JwDistribution;
use AnzuSystems\CoreDamBundle\Helper\StringHelper;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class JwDistributionPubDecorator implements DistributionPubDecoratorInterface
{
    private const string TYPE = 'jwVideo';
    private const string FALLBACK_TEMPLATE = 'https://cdn.jwplayer.com/players/%s.html';

    private JwDistribution $distribution;
    public static function getInstance(JwDistribution $distribution): self
    {
        return (new self())
            ->setDistribution($distribution)
        ;
    }

    public function getDistribution(): JwDistribution
    {
        return $this->distribution;
    }

    public function setDistribution(JwDistribution $distribution): self
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
    public function getDirectUrl(): string
    {
        return $this->distribution->getDirectSourceUrl();
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
