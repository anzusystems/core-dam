<?php

declare(strict_types=1);

namespace App\Elasticsearch\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiInfiniteResponseList;
use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Traits\ValidatorAwareTrait;
use AnzuSystems\CoreDamBundle\Elasticsearch\ElasticSearch;
use AnzuSystems\CoreDamBundle\Elasticsearch\SearchDto\AssetAdmSearchLicenceCollectionDto;
use AnzuSystems\CoreDamBundle\Entity\Asset;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetStatus;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetType;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Model\Ugc\Legacy\ImageListDto;
use Doctrine\Common\Collections\ArrayCollection;

final class ImageUgcLegacyElasticsearchDecorator
{
    use ValidatorAwareTrait;

    public function __construct(
        private readonly ElasticSearch $elasticSearch,
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
        $this->validator->validate($searchDto);

        /** @var ApiInfiniteResponseList<Asset> $list */
        $list = $this->elasticSearch->searchInfiniteList($searchDto, $licence->getExtSystem());
        $data = array_map(
            static function (Asset $asset): ?ImageListDto {
                $mainFile = $asset->getMainFile();

                return $mainFile instanceof ImageFile ? ImageListDto::getInstance($mainFile) : null;
            },
            $list->getData()
        );
        $data = array_filter($data);

        return (new ApiResponseList())
            ->setBigTable(true)
            ->setTotalCount($list->isHasNextPage() ? $apiUgcLegacyParams->getLimit() + 1 : count($data))
            ->setData($data);
    }

    private function createSearchDto(
        AssetLicence $licence,
        ApiUgcLegacyParams $apiUgcLegacyParams,
    ): AssetAdmSearchLicenceCollectionDto {
        $searchDto = new AssetAdmSearchLicenceCollectionDto();
        $searchDto
            ->setLicences(new ArrayCollection([$licence]))
            ->setType([AssetType::Image->toString()])
            ->setText($apiUgcLegacyParams->getText())
            ->setStatus([AssetStatus::WithFile->toString()])
            ->setOrder(['modifiedAt' => 'desc', 'id' => 'desc'])
            ->setOffset($apiUgcLegacyParams->getOffset())
            ->setLimit($apiUgcLegacyParams->getLimit())
            ->setCreatedAtFrom($apiUgcLegacyParams->getCreatedAtFrom())
            ->setCreatedAtUntil($apiUgcLegacyParams->getCreatedAtUntil());

        return $searchDto
        ;
    }
}
