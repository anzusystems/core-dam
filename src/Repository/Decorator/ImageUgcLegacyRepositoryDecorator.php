<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\ApiFilter\ApiResponseList;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CoreDamBundle\ApiFilter\AssetStatusForAssetFileApiParams;
use AnzuSystems\CoreDamBundle\ApiFilter\LicensedEntityApiParams;
use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use AnzuSystems\CoreDamBundle\Model\Enum\AssetStatus;
use AnzuSystems\CoreDamBundle\Repository\CustomFilter\CustomAssetStatusForAssetFileFilter;
use AnzuSystems\CoreDamBundle\Repository\CustomFilter\LicensedEntityFilter;
use AnzuSystems\CoreDamBundle\Repository\ImageFileRepository;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\ApiFilter\ApiUgcLegacyParams;
use App\Elasticsearch\Decorator\ImageUgcLegacyElasticsearchDecorator;
use App\Model\Ugc\Legacy\ImageDetailDto;
use App\Model\Ugc\Legacy\ImageListDto;
use Doctrine\ORM\Exception\ORMException;

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
     * @throws ORMException
     */
    public function searchList(AssetLicence $licence, ApiUgcLegacyParams $apiUgcLegacyParams): ApiResponseList
    {
        // A.) Filter applied, use ES
        if ($apiUgcLegacyParams->isFilterApplied()) {
            return $this->elasticSearch->searchList($licence, $apiUgcLegacyParams);
        }

        // B.) Specific ids send, fetch directly by ids and licence
        if ($apiUgcLegacyParams->getIds()) {
            $imageFiles = $this->imageFileRepo->findByLicenceAndIds($licence, $apiUgcLegacyParams->getIds());
            /** @psalm-var ApiResponseList<ImageListDto> $responseList */
            $responseList = new ApiResponseList();

            return $responseList
                ->setTotalCount($imageFiles->count())
                ->setData(array_map(
                    ImageDetailDto::getInstance(...),
                    $imageFiles->getValues()
                ))
            ;
        }

        // C.) No filter applied and no specific IDS, use api filter on DB
        $apiParams = (new ApiParams())
            ->setOrder([
                'modifiedAt' => 'desc',
                'id' => 'desc',
            ])
            ->setLimit($apiUgcLegacyParams->getLimit())
            ->setOffset($apiUgcLegacyParams->getOffset())
        ;
        $apiParams = LicensedEntityApiParams::applyLicenceCustomFilter($apiParams, $licence);
        $apiParams = AssetStatusForAssetFileApiParams::applyCustomFilter($apiParams, AssetStatus::WithFile);
        $responseList = $this->imageFileRepo->findByApiParams(
            apiParams: $apiParams,
            customFilters: [
                new LicensedEntityFilter(),
                new CustomAssetStatusForAssetFileFilter(),
            ]
        );

        return $responseList
            ->setData(array_map(
                ImageListDto::getInstance(...),
                $responseList->getData()
            ));
    }
}
