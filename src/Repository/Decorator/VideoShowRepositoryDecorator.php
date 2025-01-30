<?php

declare(strict_types=1);

namespace App\Repository\Decorator;

use AnzuSystems\CommonBundle\ApiFilter\ApiInfiniteResponseList;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Entity\VideoShow;
use App\App;
use App\Model\Domain\VideoShow\VideoShowPubDecorator;
use App\Model\Request\ApiPubParams;
use App\Repository\VideoShowRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class VideoShowRepositoryDecorator
{
    public function __construct(
        private VideoShowRepository $videoShowRepository,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function getList(PublicExport $publicExport, ApiPubParams $apiParams): ApiInfiniteResponseList
    {
        $data = $this->videoShowRepository->getByPublicExport($publicExport, $apiParams);

        return (new ApiInfiniteResponseList())
            ->setData($data->map(
                fn (VideoShow $videoShow): VideoShowPubDecorator => VideoShowPubDecorator::getInstance($videoShow)
            )->slice(App::ZERO, $apiParams->getLimit()))
            ->setHasNextPage(count($data) > $apiParams->getLimit())
        ;
    }
}
