<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Elasticsearch\Decorator\ImageUgcLegacyElasticsearchDecorator;
use App\Model\Ugc\Legacy\ImageListDto;

final readonly class ImageUgcLegacyRepositoryDecorator
{
    public function __construct(
        private ImageUgcLegacyElasticsearchDecorator $elasticSearch,
        private ImageFileRepository $imageFileRepo,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    public function searchList(AssetLicence $licence, ApiUgcLegacyParams $apiUgcLegacyParams): ApiResponseList
    {
        if (empty($apiUgcLegacyParams->getIds())) {
            return $this->elasticSearch->searchList($licence, $apiUgcLegacyParams);
        }

        $imageFiles = $this->imageFileRepo->findByLicenceAndIds($licence, $apiUgcLegacyParams->getIds());

        return (new ApiResponseList())
            ->setTotalCount($imageFiles->count())
            ->setData(array_map(
                fn (ImageFile $imageFile) => ImageListDto::getInstance($imageFile),
                $imageFiles->getValues()
            ))
        ;
    }
}
