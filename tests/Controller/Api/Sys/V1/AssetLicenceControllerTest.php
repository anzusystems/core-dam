<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Sys\V1;

use AnzuSystems\CoreDamBundle\Entity\AssetLicence;
use App\DataFixtures\AssetLicenceFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\ApiClientFirewall;
use App\Tests\data\Model\AssetLicenceSysUrl;
use Symfony\Component\HttpFoundation\Response;

final class AssetLicenceControllerTest extends AbstractApiController
{
    public function testUpsertLicence(): void
    {
        // 1. Try to create a new licence
        $client = $this->getClient(ApiClientFirewall::ID_BLOG_SYS_API, ApiClientFirewall::Sys);
        $extId = AssetLicenceFixtures::BLOG_EXT_ID + 1;
        $this->assertUserLicence(UserFixtures::USER_TWO_SSO_ID, $extId, false);
        $response = $client->put(AssetLicenceSysUrl::upsertLicence(), [
            'extId' => $extId,
            'limited' => true,
            'users' => [UserFixtures::USER_TWO_SSO_ID],
        ]);
        $createdJson = $this->assertResponseAndGetJsonContent($response, Response::HTTP_CREATED);
        $this->assertEquals($extId, $createdJson['extId']);
        $this->assertTrue($createdJson['limitedFiles']);
        $this->assertSame(4, $createdJson['extSystem']);
        $this->assertUserLicence(UserFixtures::USER_TWO_SSO_ID, $extId);

        // 2. Try to upsert existing licence
        $this->assertUserLicence(UserFixtures::USER_ONE_SSO_ID, $extId, false);
        $response = $client->put(AssetLicenceSysUrl::upsertLicence(), [
            'extId' => $extId,
            'limited' => false,
            'users' => [UserFixtures::USER_ONE_SSO_ID],
        ]);
        $updatedJson = $this->assertResponseAndGetJsonContent($response);
        $this->assertEquals($createdJson['id'], $updatedJson['id']);
        $this->assertEquals($extId, $updatedJson['extId']);
        $this->assertFalse($updatedJson['limitedFiles']);
        $this->assertSame(4, $updatedJson['extSystem']);
        $this->assertUserLicence(UserFixtures::USER_ONE_SSO_ID, $extId);
    }

    private function assertUserLicence(int $userId, int $extId, bool $expectedToAssigned = true): void
    {
        $userFromDb = $this->entityManager->find(User::class, $userId);
        $userLicenceInDb = $userFromDb->getAssetLicences()->filter(
            fn (AssetLicence $licence) => ((string) $extId) === $licence->getExtId() && 4 === $licence->getExtSystem()->getId()
        )->first() ?? null;
        $this->assertSame($expectedToAssigned, is_object($userLicenceInDb) && $userLicenceInDb::class === AssetLicence::class);
    }
}
