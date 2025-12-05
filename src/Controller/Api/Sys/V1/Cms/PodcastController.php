<?php

declare(strict_types=1);

namespace App\Controller\Api\Sys\V1\Cms;

use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\Podcast;
use App\Model\Domain\Podcast\PodcastCmsSysDecorator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Tag('Podcast')]
#[Route('/cms/podcast', 'sys_podcast_cms_')]
final class PodcastController extends AbstractApiController
{
    #[Route('/{podcast}', 'get_one', methods: [Request::METHOD_GET])]
    public function getList(Podcast $podcast): JsonResponse
    {
        return $this->okResponse(PodcastCmsSysDecorator::getInstance($podcast));
    }
}
