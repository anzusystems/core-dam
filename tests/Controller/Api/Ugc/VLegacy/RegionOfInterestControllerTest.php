<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Ugc\VLegacy;

use AnzuSystems\CoreDamBundle\Entity\RegionOfInterest;
use AnzuSystems\CoreDamBundle\Repository\RegionOfInterestRepository;
use App\DataFixtures\ImageFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\Controller\Api\AbstractApiControllerTest;
use App\Tests\data\Model\RegionOfInterestUgcLegacyUrl;
use Doctrine\ORM\NonUniqueResultException;

final class RegionOfInterestControllerTest extends AbstractApiControllerTest
{
    private RegionOfInterest $regionOfInterest;

    /**
     * @throws NonUniqueResultException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->regionOfInterest = $this->getService(RegionOfInterestRepository::class)
            ->findByImageIdAndPosition(ImageFixtures::IMAGE_1_ID, 0);
    }

    public function testList(): void
    {
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->get(RegionOfInterestUgcLegacyUrl::getListPath(ImageFixtures::IMAGE_1_ID));
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertListResponse($json, 1);
        $this->assertSame($json['data'][0]['id'], $this->regionOfInterest->getId());
    }

    public function testGetOne(): void
    {
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->get(RegionOfInterestUgcLegacyUrl::getOnePath($this->regionOfInterest->getId()));
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame($this->regionOfInterest->getId(), $json['id']);
        $this->assertEquals($this->regionOfInterest->getPointX(), $json['pointX']);
        $this->assertEquals($this->regionOfInterest->getPointY(), $json['pointY']);
        $this->assertEquals($this->regionOfInterest->getPercentageWidth(), $json['percentageWidth']);
        $this->assertEquals($this->regionOfInterest->getPercentageHeight(), $json['percentageHeight']);
    }

    public function testUpdate(): void
    {
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->put(RegionOfInterestUgcLegacyUrl::getUpdatePath($this->regionOfInterest->getId()), [
            'id' => $this->regionOfInterest->getId(),
            'pointX' => 1,
            'pointY' => 2,
            'percentageWidth' => 0.1,
            'percentageHeight' => 0.2,
            'position' => $this->regionOfInterest->getPosition(),
        ]);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame($this->regionOfInterest->getId(), $json['id']);
        $this->assertEquals(1, $json['pointX']);
        $this->assertEquals(2, $json['pointY']);
        $this->assertEquals(0.1, $json['percentageWidth']);
        $this->assertEquals(0.2, $json['percentageHeight']);
    }
}
