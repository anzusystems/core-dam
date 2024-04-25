<?php

declare(strict_types=1);

namespace App\Model\Dto\Artemis;

use AnzuSystems\SerializerBundle\Attributes\Serialize;

final class ArtemisMediaMetaDto
{
    #[Serialize]
    private int $articleId;

    #[Serialize]
    private string $articleAdminUrl;

    #[Serialize]
    private string $mediaAdminUrl;

    #[Serialize]
    private string $articleUrl;

    public function __construct()
    {
        $this->setArticleId(0);
        $this->setArticleAdminUrl('');
        $this->setMediaAdminUrl('');
        $this->setArticleUrl('');
    }

    public function getArticleId(): int
    {
        return $this->articleId;
    }

    public function setArticleId(int $articleId): self
    {
        $this->articleId = $articleId;

        return $this;
    }

    public function getArticleAdminUrl(): string
    {
        return $this->articleAdminUrl;
    }

    public function setArticleAdminUrl(string $articleAdminUrl): self
    {
        $this->articleAdminUrl = $articleAdminUrl;

        return $this;
    }

    public function getMediaAdminUrl(): string
    {
        return $this->mediaAdminUrl;
    }

    public function setMediaAdminUrl(string $mediaAdminUrl): self
    {
        $this->mediaAdminUrl = $mediaAdminUrl;

        return $this;
    }

    public function getArticleUrl(): string
    {
        return $this->articleUrl;
    }

    public function setArticleUrl(string $articleUrl): self
    {
        $this->articleUrl = $articleUrl;

        return $this;
    }
}
