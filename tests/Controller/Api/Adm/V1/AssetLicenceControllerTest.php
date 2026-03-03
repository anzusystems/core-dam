<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Adm\V1;

use AnzuSystems\CoreDamBundle\DataFixtures\AuthorFixtures;
use App\App;
use App\DataFixtures\AssetLicenceFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\Controller\Api\AbstractApiController;
use App\Tests\data\Model\AssetLicenceAdmUrl;
use Symfony\Component\HttpFoundation\Response;

final class AssetLicenceControllerTest extends AbstractApiController
{
    public function testCreateLicenceWithInternalRule(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $response = $client->post(AssetLicenceAdmUrl::createLicence(), [
            'name' => 'Test Internal Rule Licence',
            'extId' => '999',
            'extSystem' => App::BLOG_EXT_SYSTEM_ID,
            'internalRule' => [
                'active' => true,
                'markAsInternalSince' => '2024-06-01T00:00:00.000000Z',
            ],
            'internalRuleAuthors' => [AuthorFixtures::AUTHOR_1],
            'internalRuleUsers' => [UserFixtures::USER_ONE_SSO_ID],
        ]);

        $json = $this->assertResponseAndGetJsonContent($response, Response::HTTP_CREATED);

        $this->assertArrayHasKey('internalRule', $json);
        $this->assertTrue($json['internalRule']['active']);
        $this->assertNotNull($json['internalRule']['markAsInternalSince']);
        $this->assertContains(AuthorFixtures::AUTHOR_1, $json['internalRuleAuthors']);
        $this->assertContains(UserFixtures::USER_ONE_SSO_ID, $json['internalRuleUsers']);
    }

    public function testUpdateLicenceAddsInternalRuleAuthorsAndUsers(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $licenceId = AssetLicenceFixtures::BLOG_ONE_LICENCE_ID;

        $response = $client->put(AssetLicenceAdmUrl::updateLicence($licenceId), [
            'id' => $licenceId,
            'name' => 'Anzulák: Blog plný radosti',
            'extId' => (string) AssetLicenceFixtures::BLOG_ONE_EXT_ID,
            'extSystem' => App::BLOG_EXT_SYSTEM_ID,
            'internalRule' => [
                'active' => true,
                'markAsInternalSince' => null,
            ],
            'internalRuleAuthors' => [AuthorFixtures::AUTHOR_1, AuthorFixtures::AUTHOR_2],
            'internalRuleUsers' => [UserFixtures::USER_ONE_SSO_ID],
        ]);

        $json = $this->assertResponseAndGetJsonContent($response);

        $this->assertSame($licenceId, $json['id']);
        $this->assertArrayHasKey('internalRule', $json);
        $this->assertTrue($json['internalRule']['active']);
        $this->assertNull($json['internalRule']['markAsInternalSince']);
        $this->assertContains(AuthorFixtures::AUTHOR_1, $json['internalRuleAuthors']);
        $this->assertContains(AuthorFixtures::AUTHOR_2, $json['internalRuleAuthors']);
        $this->assertContains(UserFixtures::USER_ONE_SSO_ID, $json['internalRuleUsers']);
    }

    public function testUpdateLicenceRemovesInternalRuleAuthorsAndUsers(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $licenceId = AssetLicenceFixtures::BLOG_ONE_LICENCE_ID;

        // First set some authors and users
        $client->put(AssetLicenceAdmUrl::updateLicence($licenceId), [
            'id' => $licenceId,
            'name' => 'Anzulák: Blog plný radosti',
            'extId' => (string) AssetLicenceFixtures::BLOG_ONE_EXT_ID,
            'extSystem' => App::BLOG_EXT_SYSTEM_ID,
            'internalRule' => ['active' => true],
            'internalRuleAuthors' => [AuthorFixtures::AUTHOR_1],
            'internalRuleUsers' => [UserFixtures::USER_ONE_SSO_ID],
        ]);

        // Now clear them
        $response = $client->put(AssetLicenceAdmUrl::updateLicence($licenceId), [
            'id' => $licenceId,
            'name' => 'Anzulák: Blog plný radosti',
            'extId' => (string) AssetLicenceFixtures::BLOG_ONE_EXT_ID,
            'extSystem' => App::BLOG_EXT_SYSTEM_ID,
            'internalRule' => ['active' => false],
            'internalRuleAuthors' => [],
            'internalRuleUsers' => [],
        ]);

        $json = $this->assertResponseAndGetJsonContent($response);

        $this->assertSame($licenceId, $json['id']);
        $this->assertFalse($json['internalRule']['active']);
        $this->assertEmpty($json['internalRuleAuthors']);
        $this->assertEmpty($json['internalRuleUsers']);
    }

    public function testGetLicenceReturnsInternalRuleFields(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $licenceId = AssetLicenceFixtures::BLOG_ONE_LICENCE_ID;

        $response = $client->get(AssetLicenceAdmUrl::getLicence($licenceId));

        $json = $this->assertResponseAndGetJsonContent($response);

        $this->assertSame($licenceId, $json['id']);
        $this->assertArrayHasKey('internalRule', $json);
        $this->assertArrayHasKey('active', $json['internalRule']);
        $this->assertArrayHasKey('markAsInternalSince', $json['internalRule']);
        $this->assertArrayHasKey('internalRuleAuthors', $json);
        $this->assertArrayHasKey('internalRuleUsers', $json);
    }

    public function testCreateLicenceWithInactiveInternalRuleByDefault(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $response = $client->post(AssetLicenceAdmUrl::createLicence(), [
            'name' => 'Test Default Internal Rule',
            'extId' => '998',
            'extSystem' => App::BLOG_EXT_SYSTEM_ID,
        ]);

        $json = $this->assertResponseAndGetJsonContent($response, Response::HTTP_CREATED);

        $this->assertArrayHasKey('internalRule', $json);
        $this->assertFalse($json['internalRule']['active']);
        $this->assertNull($json['internalRule']['markAsInternalSince']);
        $this->assertEmpty($json['internalRuleAuthors']);
        $this->assertEmpty($json['internalRuleUsers']);
    }

    public function testGetLicenceReturnsNotFoundForUnknownId(): void
    {
        $client = $this->getApiClient(App::getUserIdAdmin());
        $response = $client->get(AssetLicenceAdmUrl::getLicence(999_999_999));

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testCreateLicenceRequiresAuthentication(): void
    {
        $client = $this->getApiClient();
        $response = $client->post(AssetLicenceAdmUrl::createLicence(), [
            'name' => 'Unauthenticated Licence',
            'extId' => '997',
            'extSystem' => App::BLOG_EXT_SYSTEM_ID,
        ]);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

}
