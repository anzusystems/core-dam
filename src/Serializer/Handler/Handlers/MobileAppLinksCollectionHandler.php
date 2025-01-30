<?php

declare(strict_types=1);

namespace App\Serializer\Handler\Handlers;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Serializer\Handler\Handlers\PublicLinksTagCollectionHandler;
use AnzuSystems\SerializerBundle\Metadata\Metadata;

final class MobileAppLinksCollectionHandler extends PublicLinksTagCollectionHandler
{
    private const string TITLE = 'title';
    private const string URL = 'url';

    protected function getImageFileLinks(ImageFile $imageFile, Metadata $metadata): array
    {
        $data = parent::getImageFileLinks($imageFile, $metadata);

        return array_reduce($data, static function (array|null $carry, array $item): array {
            $item = [$item[self::TITLE] => $item[self::URL]];
            return is_array($carry)
                ? array_merge($carry, $item)
                : $item;
        });
    }

    protected function serializeLinksData(
        string $type,
        string $url,
        int $requestedWidth,
        int $requestedHeight,
        string $title,
    ): string|array {
        return [
            'url' => $url,
            'title' => $title,
        ];
    }
}
