<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Ugc\VLegacy;

use AnzuSystems\CoreDamBundle\Entity\ImageFile;
use AnzuSystems\CoreDamBundle\FileSystem\FileSystemProvider;
use AnzuSystems\CoreDamBundle\FileSystem\NameGenerator\NameGenerator;
use AnzuSystems\CoreDamBundle\Helper\FileHelper;
use App\DataFixtures\ImageFixtures;
use App\DataFixtures\UserFixtures;
use App\Tests\ApiClient;
use App\Tests\Controller\Api\AbstractApiControllerTest;
use App\Tests\data\Model\ImageUgcLegacyUrl;
use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

final class ImageControllerTest extends AbstractApiControllerTest
{
    private const TEST_DATA_FILENAME = 'metadata_image.jpeg';

    private FileSystemProvider $filesystemProvider;
    private NameGenerator $nameGenerator;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystemProvider = self::getService(FileSystemProvider::class);
        $this->nameGenerator = self::getService(NameGenerator::class);
    }

    public function testSearchList()
    {
        // 1. Basic list
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->get(ImageUgcLegacyUrl::getImageSearchListPath());
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertListResponse($json, 2);
        $this->assertSame($json['data'][0]['id'], ImageFixtures::IMAGE_2_ID);
        $this->assertSame($json['data'][0]['texts']['description'], ImageFixtures::IMAGE_2_DESCRIPTION);
        $this->assertSame($json['data'][0]['authors'][0]['customAuthor'], ImageFixtures::IMAGE_2_AUTHOR);
        $this->assertSame($json['data'][1]['id'], ImageFixtures::IMAGE_1_ID);
        $this->assertSame($json['data'][1]['texts']['description'], ImageFixtures::IMAGE_1_DESCRIPTION);
        $this->assertSame($json['data'][1]['authors'][0]['customAuthor'], ImageFixtures::IMAGE_1_AUTHOR);

        // 2. List by ID
        $response = $client->get(ImageUgcLegacyUrl::getImageSearchListPath(), ['filter_in' => ['id' => ImageFixtures::IMAGE_1_ID]]);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertListResponse($json, 1);
        $this->assertSame($json['data'][0]['id'], ImageFixtures::IMAGE_1_ID);

        // 3. Search by text
        $response = $client->get(ImageUgcLegacyUrl::getImageSearchListPath(), ['text' => '2']);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertListResponse($json, 1);
        $this->assertSame($json['data'][0]['id'], ImageFixtures::IMAGE_2_ID);
    }

    public function testGetOne(): void
    {
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->get(ImageUgcLegacyUrl::getSingleImagePath(ImageFixtures::IMAGE_1_ID));
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame($json['id'], ImageFixtures::IMAGE_1_ID);
    }

    public function testUploadDuplicate(): void
    {
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $this->createImage($client, $this->getFile('text_image_200x200.jpg'), Response::HTTP_OK);
        $id = json_decode($response->getContent(), true)['id'];
        $this->assertSame(ImageFixtures::IMAGE_2_ID, $id);
    }

    /**
     * @throws FilesystemException
     */
    public function testUpload(): void
    {
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);

        // 1. Test to upload
        $uploadedFileJson = $this->uploadImage(
            $client,
            self::TEST_DATA_FILENAME,
        );
        $imageEntity = $this->entityManager->find(ImageFile::class, $uploadedFileJson['id']);
        $filesystem = $this->filesystemProvider->getFilesystemByStorable($imageEntity);
        $originImagePath = $this->nameGenerator->getPath($imageEntity->getAssetAttributes()->getFilePath());

        // Checks origin file and resizes
        $this->assertFileInFilesystemExists($filesystem, $originImagePath->getFullPath());
        $this->assertNotEmpty($imageEntity->getResizes());
        foreach ($imageEntity->getResizes() as $resize)
        {
            $this->assertFileInFilesystemExists($filesystem, $resize->getFilePath());
        }
        $this->assertCount(2, $filesystem->listContents($originImagePath->getDir())->toArray());

        // 2. Test to rotate uploaded file
        $rotate = 90;
        $response = $client->patch(ImageUgcLegacyUrl::getImageRotatePath($imageEntity->getId(), $rotate));
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $originImageAttrs = clone $imageEntity->getImageAttributes();
        $imageEntity = $this->entityManager->find(ImageFile::class, $imageEntity->getId());
        $this->assertEquals($originImageAttrs->getWidth(), $imageEntity->getImageAttributes()->getHeight());
        $this->assertEquals($originImageAttrs->getHeight(), $imageEntity->getImageAttributes()->getWidth());
        $this->assertEquals($originImageAttrs->getRatioWidth(), $imageEntity->getImageAttributes()->getRatioHeight());
        $this->assertEquals($originImageAttrs->getRatioHeight(), $imageEntity->getImageAttributes()->getRatioWidth());
        $this->assertEquals($rotate, $imageEntity->getImageAttributes()->getRotation());

        foreach ($imageEntity->getResizes() as $resize)
        {
            $this->assertFileInFilesystemExists($filesystem, $resize->getFilePath());
        }
        $this->assertCount(2, $filesystem->listContents($originImagePath->getDir())->toArray());
    }

    public function testUpdateImage(): void
    {
        $updatedDescription = 'Updated description 1';
        $updatedAuthor = 'Updated author 1';
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->put(ImageUgcLegacyUrl::getUpdateImagePath(ImageFixtures::IMAGE_1_ID) , [
            'texts' => ['description' => $updatedDescription],
            'author' => ['customAuthor' => $updatedAuthor],
        ]);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame($json['id'], ImageFixtures::IMAGE_1_ID);
        $this->assertSame($json['texts']['description'], $updatedDescription);
        $this->assertSame($json['authors'][0]['customAuthor'], $updatedAuthor);
    }

    public function testUpdateBulkImage(): void
    {
        $updatedDescription = 'Updated description';
        $updatedAuthor = 'Updated author';
        $client = $this->getClient(UserFixtures::USER_TWO_SSO_ID, true);
        $response = $client->patch(ImageUgcLegacyUrl::getUpdateBulkImagePath() , [
            [
                'id' => ImageFixtures::IMAGE_2_ID,
                'texts' => ['description' => "$updatedDescription 2"],
                'author' => ['customAuthor' => "$updatedAuthor 2"],
            ],
            [
                'id' => ImageFixtures::IMAGE_1_ID,
                'texts' => ['description' => "$updatedDescription 1"],
                'author' => ['customAuthor' => "$updatedAuthor 1"],
            ],
        ]);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame($json[0]['id'], ImageFixtures::IMAGE_2_ID);
        $this->assertSame($json[0]['texts']['description'], "$updatedDescription 2");
        $this->assertSame($json[0]['authors'][0]['customAuthor'], "$updatedAuthor 2");
        $this->assertSame($json[1]['id'], ImageFixtures::IMAGE_1_ID);
        $this->assertSame($json[1]['texts']['description'], "$updatedDescription 1");
        $this->assertSame($json[1]['authors'][0]['customAuthor'], "$updatedAuthor 1");

        $imageEntity = $this->entityManager->find(ImageFile::class, ImageFixtures::IMAGE_1_ID);
        $imageEntity->getAsset()->getAssetFlags()->setDescribed(false);
        $this->entityManager->flush();

        // Try to update only undescribed.
        $response = $client->patch(ImageUgcLegacyUrl::getUpdateBulkUndescribedImagePath() , [
            [
                'id' => ImageFixtures::IMAGE_2_ID,
                'texts' => ['description' => "not changed"],
                'author' => ['customAuthor' => "not changed"],
            ],
            [
                'id' => ImageFixtures::IMAGE_1_ID,
                'texts' => ['description' => "$updatedDescription 1-1"],
                'author' => ['customAuthor' => "$updatedAuthor 1-1"],
            ],
        ]);
        $json = $this->assertResponseAndGetJsonContent($response);
        $this->assertSame($json[0]['id'], ImageFixtures::IMAGE_2_ID);
        $this->assertSame($json[0]['texts']['description'], "$updatedDescription 2");
        $this->assertSame($json[0]['authors'][0]['customAuthor'], "$updatedAuthor 2");
        $this->assertSame($json[1]['id'], ImageFixtures::IMAGE_1_ID);
        $this->assertSame($json[1]['texts']['description'], "$updatedDescription 1-1");
        $this->assertSame($json[1]['authors'][0]['customAuthor'], "$updatedAuthor 1-1");
    }

    private function uploadImage(ApiClient $apiClient, string $fileName): array
    {
        $file = $this->getFile($fileName);

        $response = $this->createImage($apiClient, $file, Response::HTTP_CREATED);
        $id = json_decode($response->getContent(), true)['id'];
        $this->uploadChunk($apiClient, $file, $id);

        return $this->assertResponseAndGetJsonContent(
            $this->finishUpload($apiClient, $file, $id)
        );
    }

    private function createImage(
        ApiClient $apiClient,
        UploadedFile $file,
        int $expectedStatusCode,
    ): Response {
        $checksum = FileHelper::checksumFromPath((string) $file->getRealPath());
        $response = $apiClient->post(
            ImageUgcLegacyUrl::getCreatePath(),
            [
                'fileAttributes' => [
                    'mimeType' => $file->getMimeType(),
                    'partialChecksum' => $checksum,
                    'size' => $file->getSize(),
                ]
            ]
        );

        $this->assertEquals(
            $expectedStatusCode,
            $response->getStatusCode(),
            sprintf(
                'Asset create failed. Api request status code (%s) and content (%s)',
                $response->getStatusCode(),
                $response->getContent()
            )
        );

        return $response;
    }

    private function uploadChunk(
        ApiClient $apiClient,
        UploadedFile $file,
        string $imageFileId,
    ): void {
        $response = $apiClient->postChunkFile(
            ImageUgcLegacyUrl::getCreateChunkPath($imageFileId),
            $file,
            [
                'offset' => 0,
                'size' => (int) $file->getSize(),
            ]
        );

        $this->assertEquals(
            Response::HTTP_CREATED,
            $response->getStatusCode(),
            sprintf(
                'Create chunk failed. Api request status code (%s) and content (%s)',
                $response->getStatusCode(),
                $response->getContent()
            )
        );

    }

    private function finishUpload(
        ApiClient $apiClient,
        UploadedFile $file,
        string $imageFileId,
    ): Response {
        $checksum = FileHelper::checksumFromPath((string) $file->getRealPath());
        $response = $apiClient->patch(
            ImageUgcLegacyUrl::getFinishUploadPath($imageFileId),
            [
                'checksum' => $checksum
            ]
        );

        $this->assertEquals(
            Response::HTTP_OK,
            $response->getStatusCode(),
            sprintf(
                'Asset postprocess failed. Api request status code (%s) and content (%s)',
                $response->getStatusCode(),
                $response->getContent()
            )
        );

        return $response;
    }

    private function getFile(string $fileName): UploadedFile
    {
        $file = new File(ImageFixtures::DATA_PATH . $fileName);

        return new UploadedFile($file->getRealPath(), $file->getFilename());
    }

    /**
     * @throws FilesystemException
     */
    protected function assertFileInFilesystemExists(Filesystem $filesystem, string $filePath): void
    {
        $this->assertTrue(
            $filesystem->fileExists($filePath),
            sprintf(
                'File (%s) not exists',
                $filePath
            )
        );
    }
}
