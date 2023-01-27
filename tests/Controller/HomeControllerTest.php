<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use AnzuSystems\CommonBundle\Tests\AnzuWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class HomeControllerTest extends AnzuWebTestCase
{
    public function testHome(): void
    {
        self::$client->request(Request::METHOD_GET, '/');
        $this->assertResponseIsSuccessful();
    }
}
