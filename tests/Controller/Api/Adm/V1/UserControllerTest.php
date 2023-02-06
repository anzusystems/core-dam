<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\Adm\V1;

use App\App;
use App\Repository\UserRepository;
use App\Tests\Controller\Api\AbstractApiControllerTest;
use Symfony\Component\HttpFoundation\Response;

final class UserControllerTest extends AbstractApiControllerTest
{
    public function testCurrentApi(): void
    {
        $client = $this->getClient(App::getUserIdAdmin());
        $response = $client->get('/api/adm/v1/user/current');
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('id', $data);

        $userFromDb = $this->getService(UserRepository::class)->find(App::getUserIdAdmin());

        $this->assertSame(
            expected: $userFromDb->getEmail(),
            actual: $data['email'],
        );
        $this->assertSame(
            expected: $userFromDb->getRoles(),
            actual: $data['roles'],
        );
        $this->assertSame(
            expected: $userFromDb->getId(),
            actual: $data['id'],
        );
    }
}
