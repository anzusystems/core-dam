<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Repository\AssetRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Elasticsearch\Decorator\ImageUgcLegacyElasticsearchDecorator;
use App\Model\Ugc\Legacy\ImageListDto;

final readonly class ImageUgcLegacyRepositoryDecorator
{
    public function __construct(
        private ImageUgcLegacyElasticsearchDecorator $elasticSearch,
        private AssetRepository $assetRepo,
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

        $data = $this->assetRepo->findByLicenceAndIds($licence, $apiUgcLegacyParams->getIds());

        return (new ApiResponseList())
            ->setTotalCount(count($data))
            ->setData(array_map(
                fn (Asset $asset) => ImageListDto::getInstance($asset->getMainFile()),
                $data->getValues()
            ))
        ;

    }
}
