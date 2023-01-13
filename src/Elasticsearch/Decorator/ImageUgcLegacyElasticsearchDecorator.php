<?php

declare(strict_types=1);

namespace App\Elasticsearch\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\Elasticsearch\ElasticSearch;
use AnzuSystems\CoreDamBundle\Elasticsearch\SearchDto\AssetAdmSearchDto;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\CoreDamBundle\Validator\EntityValidator;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Model\Ugc\Legacy\ImageListDto;

final readonly class ImageUgcLegacyElasticsearchDecorator
{
    public function __construct(
        private ElasticSearch $elasticSearch,
        private EntityValidator $validator,
    ) {
    }

    /**
     * @throws SerializerException
     * @throws ValidationException
     */
    public function searchList(
        AssetLicence $licence,
        ApiUgcLegacyParams $apiUgcLegacyParams,
    ): ApiResponseList {
        $searchDto = $this->createSearchDto($licence, $apiUgcLegacyParams);
        $this->validator->validateDto($searchDto);

        $list = $this->elasticSearch->searchInfiniteList($searchDto, $licence->getExtSystem());
        $data = array_map(
            static fn (Asset $asset): ImageListDto => ImageListDto::getInstance($asset->getMainFile()),
            $list->getData()
        );

        return (new ApiResponseList())
            ->setBigTable(true)
            ->setTotalCount($list->isHasNextPage() ? $apiUgcLegacyParams->getLimit() + 1 : count($data))
            ->setData($data);
    }

    private function createSearchDto(
        AssetLicence $licence,
        ApiUgcLegacyParams $apiUgcLegacyParams,
    ): AssetAdmSearchDto {
        return (new AssetAdmSearchDto())
            ->setType([AssetType::Image->toString()])
            ->setText($apiUgcLegacyParams->getText())
            ->setLicences([$licence])
            ->setOrder(['modifiedAt' => 'desc'])
            ->setOffset($apiUgcLegacyParams->getOffset())
            ->setLimit($apiUgcLegacyParams->getLimit())
            ->setCreatedAtFrom($apiUgcLegacyParams->getCreatedAtFrom())
            ->setCreatedAtUntil($apiUgcLegacyParams->getCreatedAtUntil())
        ;
    }
}
