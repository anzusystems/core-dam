<?php

declare(strict_types=1);

namespace App\Controller;

use AnzuSystems\CoreDamBundle\Controller\Api\AbstractApiController;
use AnzuSystems\CoreDamBundle\Entity\PublicExport;
use AnzuSystems\CoreDamBundle\Repository\PublicExportRepository;
use AnzuSystems\SerializerBundle\Context\SerializationContext;
use AnzuSystems\SerializerBundle\Exception\SerializerException;
use App\Controller\Traits\PubCacheTrait;
use App\Exception\PubNotFoundHttpException;
use App\Model\Request\CacheSettings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractApiPubController extends AbstractApiController
{
    use PubCacheTrait;

    private PublicExportRepository $publicExportRepository;

    #[Required]
    public function setPublicExportRepository(PublicExportRepository $publicExportRepository): void
    {
        $this->publicExportRepository = $publicExportRepository;
    }

    /**
     * @throws SerializerException
     */
    protected function getResponse(
        array | object $data,
        int $statusCode = JsonResponse::HTTP_OK,
        ?CacheSettings $cacheSettings = null,
    ): JsonResponse {
        $response = new JsonResponse(
            $this->serializer->serialize($data, SerializationContext::create()->setSerializeNulls(false)),
            $statusCode,
            [],
            true
        );
        if ($cacheSettings) {
            $this->setCache($response, $cacheSettings);
        }

        return $response;
    }

    protected function okCachedResponse(
        array|object $data,
        CacheSettings $cacheSettings,
    ): JsonResponse {
        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->getResponse($data, cacheSettings: $cacheSettings);
        if ($this->getParameter('app_cache_proxy_enabled')) {
            $this->setCache($response, $cacheSettings);
        }

        return $response;
    }

    /**
     * @throws PubNotFoundHttpException
     */
    protected function getPublicExportBySlug(string $slug): PublicExport
    {
        $publicExport = $this->publicExportRepository->findOneBySlug($slug);
        if ($publicExport instanceof PublicExport) {
            return $publicExport;
        }

        throw new PubNotFoundHttpException('PublicExport not found');
    }
}
