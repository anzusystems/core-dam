<?php

declare(strict_types=1);

namespace App\Tests\HttpClient;

use App\App;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractMock extends MockHttpClient
{
    private const TEST_DATA_FILES = '/tests/data/files/';

    protected function getTestDataFile(string $file): string
    {
        return file_get_contents($this->getPath($file));
    }

    protected function getTestDataMime(string $file): string
    {
        return (string) mime_content_type($this->getPath($file));
    }

    protected function getPath(string $file): string
    {
        return App::getProjectDir() . self::TEST_DATA_FILES . $file;
    }
}
