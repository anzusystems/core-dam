<?php

declare(strict_types=1);

namespace App\Model\Domain\Distribution;

use AnzuSystems\CoreDamBundle\Model\Domain\Distribution\DistributionDataUrl;
use AnzuSystems\SerializerBundle\Attributes\Serialize;

class ArtemisDistributionData
{
    #[Serialize]
    protected DistributionDataUrl $articleWebUrl;

    #[Serialize]
    protected DistributionDataUrl $articleAdminUrl;

    #[Serialize]
    protected DistributionDataUrl $mediaAdminUrl;

    public function __construct()
    {
        $this->setArticleWebUrl(new DistributionDataUrl());
        $this->setArticleAdminUrl(new DistributionDataUrl());
        $this->setMediaAdminUrl(new DistributionDataUrl());
    }

    public function getArticleWebUrl(): DistributionDataUrl
    {
        return $this->articleWebUrl;
    }

    public function setArticleWebUrl(DistributionDataUrl $articleWebUrl): self
    {
        $this->articleWebUrl = $articleWebUrl;
        return $this;
    }

    public function getArticleAdminUrl(): DistributionDataUrl
    {
        return $this->articleAdminUrl;
    }

    public function setArticleAdminUrl(DistributionDataUrl $articleAdminUrl): self
    {
        $this->articleAdminUrl = $articleAdminUrl;
        return $this;
    }

    public function getMediaAdminUrl(): DistributionDataUrl
    {
        return $this->mediaAdminUrl;
    }

    public function setMediaAdminUrl(DistributionDataUrl $mediaAdminUrl): self
    {
        $this->mediaAdminUrl = $mediaAdminUrl;
        return $this;
    }
}
