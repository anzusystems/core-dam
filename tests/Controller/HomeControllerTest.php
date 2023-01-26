<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use AnzuSystems\CommonBundle\Tests\AnzuWebTestCase;

final class HomeControllerTest extends AnzuWebTestCase
{
    public function testHome(): void
    {
        self::$client->request('get', '/');
        $this->assertResponseIsSuccessful();
    }
}
