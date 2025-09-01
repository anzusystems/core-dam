# Entity CRUD Creation Rules

## Overview
These rules define the standard structure and implementation patterns for creating CRUD entities in the Anzu CMS system.

## Rule: Directory Structure
When creating a new entity (e.g., `Example`), you MUST create the following directory structure in `src/Domain/Example/`:

```
src/Domain/Example/
├── Controller/
│   └── Api/
│       └── Adm/
│           └── V1/
├── Entity/
├── Facade/
├── Manager/
├── Repository/
```

## Rule: Entity Creation (`src/Domain/Example/Entity/Example.php`)

### MUST Implement Interfaces
All entities MUST implement these interfaces:
- `AnzuSystems\Contracts\Entity\Interfaces\IdentifiableInterface`
- `AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface`
- `AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface`

### MUST Use Traits
All entities MUST use these traits:
- `AnzuSystems\Contracts\Entity\Traits\IdentityIntTrait`
- `AnzuSystems\Contracts\Entity\Traits\TimeTrackingTrait`
- `AnzuSystems\Contracts\Entity\Traits\UserTrackingTrait`

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example\Entity;

use AnzuSystems\Contracts\Entity\Interfaces\IdentifiableInterface;
use AnzuSystems\Contracts\Entity\Interfaces\TimeTrackingInterface;
use AnzuSystems\Contracts\Entity\Interfaces\UserTrackingInterface;
use AnzuSystems\Contracts\Entity\Traits\IdentityIntTrait;
use AnzuSystems\Contracts\Entity\Traits\TimeTrackingTrait;
use AnzuSystems\Contracts\Entity\Traits\UserTrackingTrait;
use App\Domain\Example\Repository\ExampleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExampleRepository::class)]
class Example implements
    IdentifiableInterface,
    UserTrackingInterface,
    TimeTrackingInterface
{
    use IdentityIntTrait;
    use TimeTrackingTrait;
    use UserTrackingTrait;
}
```

## Rule: Repository Creation (`src/Domain/Example/Repository/ExampleRepository.php`)

### MUST Requirements
- MUST extend `App\Repository\AbstractAnzuRepository`
- Entity MUST reference this repository in `#[ORM\Entity]` attribute
- MUST implement the `getEntityClass()` method that returns the entity class

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example\Repository;

use App\Domain\Example\Entity\Example;
use App\Repository\AbstractAnzuRepository;

/**
 * @extends AbstractAnzuRepository<Example>
 */
final class ExampleRepository extends AbstractAnzuRepository
{
    protected function getEntityClass(): string
    {
        return Example::class;
    }
}
```

## Rule: Manager Creation (`src/Domain/Example/Manager/ExampleManager.php`)

### MUST Requirements
- MUST extend `AnzuSystems\CommonBundle\Domain\AbstractManager`
- MUST implement `create()`, `update()`, and `delete()` methods
- MUST use `$this->trackCreation()` for new entities
- MUST use `$this->trackModification()` for updates
- MUST use `$this->entityManager` for persistence operations
- MUST use `$this->flush($flush)` for database operations

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example\Manager;

use AnzuSystems\CommonBundle\Domain\AbstractManager;
use App\Domain\Example\Entity\Example;

final class ExampleManager extends AbstractManager
{
    public function create(Example $example, bool $flush = true): Example
    {
        $this->trackCreation($example);
        $this->entityManager->persist($example);
        $this->flush($flush);

        return $example;
    }

    public function update(Example $example, Example $newExample, bool $flush = true): Example
    {
        $this->trackModification($example);
        $example
            ->setSite($newExample->getSite())
            ->setSiteGroup($newExample->getSiteGroup())
        ;
        $example->getTexts()
            ->setTitle($newExample->getTexts()->getTitle())
            ->setDescription($newExample->getTexts()->getDescription())
        ;
        $this->flush($flush);

        return $example;
    }

    public function delete(Example $example, bool $flush = true): void
    {
        $this->entityManager->remove($example);
        $this->flush($flush);
    }
}
```

## Rule: Facade Creation (`src/Domain/Example/Facade/ExampleFacade.php`)

### MUST Requirements
- MUST be declared as `final readonly class`
- MUST inject `Validator` and `Manager` via constructor
- MUST implement `create()`, `update()`, and `delete()` methods
- MUST validate entities using `$this->validator->validate()`
- MUST handle `ValidationException` in method signatures

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example\Facade;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Validator\Validator;
use App\Domain\Example\Entity\Example;
use App\Domain\Example\Manager\ExampleManager;

final readonly class ExampleFacade
{
    public function __construct(
        private Validator $validator,
        private ExampleManager $manager,
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function create(Example $example): Example
    {
        $this->validator->validate($example);
        $this->manager->create($example);

        return $example;
    }

    /**
     * @throws ValidationException
     */
    public function update(Example $example, Example $newExample): Example
    {
        $this->validator->validate($newExample, $example);
        $this->manager->update($example, $newExample);

        return $example;
    }

    public function delete(Example $example): void
    {
        $this->manager->delete($example);
    }
}
```

## Rule: Controller Creation (`src/Domain/Example/Controller/Api/Adm/V1/ExampleController.php`)

### MUST Requirements
- MUST be declared as `final class`
- MUST extend `AbstractApiController`
- MUST inject `Facade` and `Repository` via constructor
- MUST implement standard CRUD endpoints: `getOne`, `getList`, `create`, `update`, `delete`
- MUST use proper OpenAPI attributes for documentation
- MUST implement permission checks using `Permission` constants
- MUST use `App::throwOnReadOnlyMode()` for write operations
- MUST use `AuditLogResourceHelper::setResourceByEntity()` for audit logging

### OpenAPI Attributes Requirements
- **getOne**: `#[OAParameterPath('entity'), OAResponse(Entity::class), OAResponseNotFound, OAResponseForbidden, OAResponseUnauthorized]`
- **getList**: `#[OAGetList(Entity::class), OAResponseForbidden, OAResponseUnauthorized]`
- **create**: `#[OARequest(Entity::class), OAResponseCreated(Entity::class), OAResponseValidation, OAResponseNotFound, OAResponseForbidden, OAResponseUnauthorized]`
- **update**: `#[OAParameterPath('entity'), OARequest(Entity::class), OAResponse(Entity::class), OAResponseValidation, OAResponseNotFound, OAResponseForbidden, OAResponseUnauthorized]`
- **delete**: `#[OAParameterPath('entity'), OAResponseDeleted, OAResponseForbidden, OAResponseUnauthorized, OAResponseNotFound]`

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Domain\Example\Controller\Api\Adm\V1;

use AnzuSystems\CommonBundle\ApiFilter\ApiParams;
use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\CommonBundle\Log\Helper\AuditLogResourceHelper;
use AnzuSystems\CommonBundle\Model\OpenApi\Get\OAGetList;
use AnzuSystems\CommonBundle\Model\OpenApi\Parameter\OAParameterPath;
use AnzuSystems\CommonBundle\Model\OpenApi\Request\OARequest;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponse;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseCreated;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseDeleted;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseForbidden;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseNotFound;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseUnauthorized;
use AnzuSystems\CommonBundle\Model\OpenApi\Response\OAResponseValidation;
use AnzuSystems\Contracts\Exception\AppReadOnlyModeException;
use AnzuSystems\SerializerBundle\Attributes\SerializeParam;
use App\App;
use App\Controller\AbstractApiController;
use App\Domain\Example\Entity\Example;
use App\Domain\Example\Facade\ExampleFacade;
use App\Domain\Example\Repository\ExampleRepository;
use App\Security\Permission;
use Doctrine\ORM\Exception\ORMException;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/example', 'adm_example_v1_')]
#[OA\Tag('Example')]
final class ExampleController extends AbstractApiController
{
    public function __construct(
        private readonly ExampleFacade $exampleFacade,
        private readonly ExampleRepository $exampleRepo,
    ) {
    }

    /**
     * Get one item.
     */
    #[Route('/{example}', 'get_one', ['example' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    #[OAParameterPath('example'), OAResponse(Example::class), OAResponseNotFound, OAResponseForbidden, OAResponseUnauthorized]
    public function getOne(Example $example): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::CMS_EXAMPLE_READ, $example);

        return $this->okResponse($example);
    }

    /**
     * Get list of items.
     *
     * @throws ORMException
     */
    #[Route('', 'get_list', methods: [Request::METHOD_GET])]
    #[OAGetList(Example::class), OAResponseForbidden, OAResponseUnauthorized]
    public function getList(ApiParams $apiParams): JsonResponse
    {
        $this->denyAccessUnlessGranted(Permission::CMS_EXAMPLE_READ);

        return $this->okResponse(
            $this->exampleRepo->findByApiParams($apiParams),
        );
    }

    /**
     * Create item.
     *
     * @throws ValidationException|AppReadOnlyModeException
     */
    #[Route('', 'create', methods: [Request::METHOD_POST])]
    #[OARequest(Example::class), OAResponseCreated(Example::class), OAResponseValidation, OAResponseNotFound, OAResponseForbidden, OAResponseUnauthorized]
    public function create(Request $request, #[SerializeParam] Example $example): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(Permission::CMS_EXAMPLE_CREATE, $example);
        $example = $this->exampleFacade->create($example);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $example);

        return $this->createdResponse($example);
    }

    /**
     * Update item.
     *
     * @throws ValidationException|AppReadOnlyModeException
     */
    #[Route('/{example}', 'update', ['example' => Requirement::DIGITS], methods: [Request::METHOD_PUT])]
    #[OAParameterPath('example'), OARequest(Example::class), OAResponse(Example::class), OAResponseValidation, OAResponseNotFound, OAResponseForbidden, OAResponseUnauthorized]
    public function update(Request $request, Example $example, #[SerializeParam] Example $newExample): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(Permission::CMS_EXAMPLE_UPDATE, $example);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $example);

        return $this->okResponse(
            $this->exampleFacade->update($example, $newExample)
        );
    }

    /**
     * Delete item.
     *
     * @throws AppReadOnlyModeException
     */
    #[Route('/{example}', 'delete', ['example' => Requirement::DIGITS], methods: [Request::METHOD_DELETE])]
    #[OAParameterPath('example'), OAResponseDeleted, OAResponseForbidden, OAResponseUnauthorized, OAResponseNotFound]
    public function delete(Request $request, Example $example): JsonResponse
    {
        App::throwOnReadOnlyMode();
        $this->denyAccessUnlessGranted(Permission::CMS_EXAMPLE_DELETE, $example);
        AuditLogResourceHelper::setResourceByEntity(request: $request, entity: $example);

        $this->exampleFacade->delete($example);

        return $this->noContentResponse();
    }
}
```

## Rule: Required Additional Steps

### MUST Add Permissions
You MUST add appropriate permissions to `App\Security\Permission` class:
- `CMS_ENTITYNAME_READ`
- `CMS_ENTITYNAME_CREATE`
- `CMS_ENTITYNAME_UPDATE`
- `CMS_ENTITYNAME_DELETE`

**Permission Class Update:**
Add constants to the end of `src/Security/Permission.php` before the closing brace:
```php
public const string CMS_ENTITYNAME_CREATE = 'cms_entityName_create';
public const string CMS_ENTITYNAME_READ = 'cms_entityName_read';
public const string CMS_ENTITYNAME_UPDATE = 'cms_entityName_update';
public const string CMS_ENTITYNAME_DELETE = 'cms_entityName_delete';
```

**Permission Configuration:**
Add to `config/packages/anzu_systems_common_permissions.yaml`:
```yaml
cms_entityName:
  ui:
  read:
  create:
  update:
  delete:
```

### MUST Implement Site Awareness (if applicable)
If your entity needs site awareness, you MUST:
- Implement `App\Domain\Site\Entity\Interfaces\SiteAwareInterface`
- Add site-related properties with proper relationships
- Use `SiteMatchesGroup` constraint for validation

#### Site and SiteGroup Relationships Implementation
When implementing entities with both `site` and `siteGroup` properties:

**Entity Requirements:**
- Implement `SiteAwareInterface` (which extends `SiteGroupAwareInterface`)
- Add `#[SiteMatchesGroup]` constraint at class level
- Include both relationships with proper validation:

```php
#[ORM\ManyToOne(targetEntity: SiteGroup::class)]
#[ORM\JoinColumn(nullable: false)]
#[AnzuAssert\NotEmptyId]
#[Serialize(handler: EntityIdHandler::class)]
private SiteGroup $siteGroup;

#[ORM\ManyToOne(targetEntity: Site::class)]
#[ORM\JoinColumn(nullable: false)]
#[AnzuAssert\NotEmptyId]
#[Serialize(handler: EntityIdHandler::class)]
private Site $site;
```

**Required Methods:**
- `getSiteGroup(): SiteGroup`
- `setSiteGroup(SiteGroup $siteGroup): self`
- `getSite(): Site`
- `setSite(Site $site): self`
- `public static function applyAllowedSitesFilter(): bool { return true; }`

**Constructor Initialization:**
```php
public function __construct()
{
    $this->setSiteGroup(new SiteGroup());
    $this->setSite(new Site());
    // ... other initializations
}
```

### MUST Handle Texts Embeddable Objects
When entities have `texts` properties with `title` and `description` fields:

**Create Separate Embeddable Class:**
Create `src/Domain/EntityName/Entity/Embeds/EntityNameTexts.php`:

```php
<?php

declare(strict_types=1);

namespace App\Domain\EntityName\Entity\Embeds;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use AnzuSystems\SerializerBundle\Attributes\Serialize;
use App\App;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Embeddable]
class EntityNameTexts
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(min: 3, max: 255, minMessage: ValidationException::ERROR_FIELD_LENGTH_MIN, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Assert\NotBlank(message: ValidationException::ERROR_FIELD_EMPTY)]
    #[Serialize]
    private string $title = App::EMPTY_STRING;

    #[ORM\Column(type: Types::STRING, length: 2_000)]
    #[Assert\Length(max: 2_000, maxMessage: ValidationException::ERROR_FIELD_LENGTH_MAX)]
    #[Serialize]
    private string $description = App::EMPTY_STRING;

    // Getters and setters...
}
```

**Entity Integration:**
```php
#[ORM\Embedded]
#[Assert\Valid]
#[Serialize]
private EntityNameTexts $texts;

public function __construct()
{
    $this->setTexts(new EntityNameTexts());
    // ... other initializations
}
```

**Manager Update Method:**
```php
public function update(Entity $entity, Entity $newEntity, bool $flush = true): Entity
{
    $this->trackModification($entity);
    $entity
        ->setSite($newEntity->getSite())
        ->setSiteGroup($newEntity->getSiteGroup())
    ;
    $entity->getTexts()
        ->setTitle($newEntity->getTexts()->getTitle())
        ->setDescription($newEntity->getTexts()->getDescription())
    ;
    $this->flush($flush);

    return $entity;
}
```

### MUST Add Validation
You MUST add appropriate validation constraints using:
- `AnzuSystems\CommonBundle\Validator\Constraints`
- `Symfony\Component\Validator\Constraints`

### MUST Configure Serialization
You MUST use `AnzuSystems\SerializerBundle\Attributes\Serialize` for API serialization.

### MUST Generate Database Migration
After creating the entity, you MUST generate and run migrations:
```bash
bin/cmd bin/console doctrine:migrations:diff
bin/cmd bin/console doctrine:migrations:migrate
```

## Rule: Naming Conventions
- Entity class names MUST be singular (e.g., `Test`, not `Tests`)
- Repository class names MUST end with `Repository`
- Manager class names MUST end with `Manager`
- Facade class names MUST end with `Facade`
- Controller class names MUST end with `Controller`
- Permission constants MUST follow pattern `CMS_ENTITY_ACTION`

## Rule: Code Quality Standards

**IMPORTANT: All entity CRUD creation MUST follow the comprehensive code quality standards defined in `/.augment/rules/code-quality-standards.md`.**

**Augment MUST apply these standards to ALL generated code, not just entities.**

## Rule: Test Fixtures Creation (`tests/data/Fixtures/EntityNameTestFixtures.php`)

### MUST Requirements
- MUST extend `AbstractTestFixtures<EntityName>`
- MUST define entity ID constants for testing
- MUST implement required methods: `getIndexKey()`, `getDependencies()`, `useCustomId()`, `load()`
- MUST create test entities with proper relationships
- MUST include at least one entity for deletion testing

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Tests\data\Fixtures;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use App\Domain\EntityName\Entity\EntityName;
use App\Domain\EntityName\Entity\Embeds\EntityNameTexts;
use App\Domain\EntityName\Facade\EntityNameFacade;
use App\Domain\Site\Fixtures\SiteFixtures;
use App\Domain\SiteGroup\Fixtures\SiteGroupFixtures;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @extends AbstractTestFixtures<EntityName>
 */
final class EntityNameTestFixtures extends AbstractTestFixtures
{
    public const int ENTITY_NAME_ONE_ID = 1;
    public const int ENTITY_NAME_TWO_ID = 2;
    public const int ENTITY_NAME_TO_DELETE_ID = 999;

    public function __construct(
        private readonly EntityNameFacade $entityNameFacade,
        private readonly SiteFixtures $siteFixtures,
        private readonly SiteGroupFixtures $siteGroupFixtures,
    ) {
    }

    public static function getIndexKey(): string
    {
        return EntityName::class;
    }

    public static function getDependencies(): array
    {
        return [
            SiteFixtures::class,
            SiteGroupFixtures::class,
        ];
    }

    public function useCustomId(): bool
    {
        return true;
    }

    /**
     * @throws ValidationException
     */
    public function load(ProgressBar $progressBar): void
    {
        foreach ($progressBar->iterate($this->getData()) as $entityName) {
            $this->entityNameFacade->create($entityName);
            $this->addToRegistry($entityName, $entityName->getId());
        }
    }

    /**
     * @return iterable<EntityName>
     */
    private function getData(): iterable
    {
        $site = $this->siteFixtures->getOneFromRegistry(SiteFixtures::SME_SITE_ID);
        $siteGroup = $this->siteGroupFixtures->getOneFromRegistry(SiteGroupFixtures::SME_FAMILY_ID);

        yield new EntityName()
            ->setId(self::ENTITY_NAME_ONE_ID)
            ->setTexts(
                new EntityNameTexts()
                    ->setTitle('EntityName test one')
                    ->setDescription('EntityName test description one')
            )
            ->setSite($site)
            ->setSiteGroup($siteGroup)
        ;

        yield new EntityName()
            ->setId(self::ENTITY_NAME_TWO_ID)
            ->setTexts(
                new EntityNameTexts()
                    ->setTitle('EntityName test two')
                    ->setDescription('EntityName test description two')
            )
            ->setSite($site)
            ->setSiteGroup($siteGroup)
        ;

        yield new EntityName()
            ->setId(self::ENTITY_NAME_TO_DELETE_ID)
            ->setTexts(
                new EntityNameTexts()
                    ->setTitle('EntityName to delete')
                    ->setDescription('EntityName to delete description')
            )
            ->setSite($site)
            ->setSiteGroup($siteGroup)
        ;
    }
}
```

## Rule: URL Factory Creation (`tests/data/UrlFactory/Api/Adm/EntityNameUrl.php`)

### MUST Requirements
- MUST be declared as `final readonly class`
- MUST define API_VERSION constant
- MUST implement all CRUD URL methods: `getOne()`, `getList()`, `create()`, `update()`, `delete()`

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Tests\data\UrlFactory\Api\Adm;

final readonly class EntityNameUrl
{
    private const int API_VERSION = 1;

    public static function getOne(int $id): string
    {
        return sprintf('/api/adm/v%d/entity-name/%d', self::API_VERSION, $id);
    }

    public static function getList(): string
    {
        return sprintf('/api/adm/v%d/entity-name', self::API_VERSION);
    }

    public static function create(): string
    {
        return sprintf('/api/adm/v%d/entity-name', self::API_VERSION);
    }

    public static function update(int $id): string
    {
        return sprintf('/api/adm/v%d/entity-name/%d', self::API_VERSION, $id);
    }

    public static function delete(int $id): string
    {
        return sprintf('/api/adm/v%d/entity-name/%d', self::API_VERSION, $id);
    }
}
```

## Rule: Controller Test Creation (`tests/Domain/EntityName/Controller/Adm/V1/EntityNameControllerTest.php`)

### MUST Requirements
- MUST extend `AbstractApiControllerTestCase`
- MUST implement all CRUD test methods: `testGetOne()`, `testCreate()`, `testUpdate()`, `testDelete()`
- MUST include validation failure tests with `testCreateValidationFailure()`
- MUST use data providers for user access testing
- MUST validate OpenAPI responses for all endpoints
- MUST include helper methods: `createValidPayload()`, `assertEntitySame()`

### Implementation Template
```php
<?php

declare(strict_types=1);

namespace App\Tests\Domain\EntityName\Controller\Adm\V1;

use AnzuSystems\CommonBundle\Exception\ValidationException;
use App\App;
use App\Domain\EntityName\Entity\EntityName;
use App\Domain\Site\Fixtures\SiteFixtures;
use App\Domain\SiteGroup\Fixtures\SiteGroupFixtures;
use App\Domain\User\Entity\User;
use App\Tests\AbstractApiControllerTestCase;
use App\Tests\data\Fixtures\EntityNameTestFixtures;
use App\Tests\data\UrlFactory\Api\Adm\EntityNameUrl;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use League\OpenAPIValidation\PSR7\OperationAddress;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

final class EntityNameControllerTest extends AbstractApiControllerTestCase
{
    #[DataProvider('userAccessOkDataProvider')]
    public function testGetOne(int $userId, int $expectedReturnStatus): void
    {
        $client = $this->getApiClient($userId);
        $url = EntityNameUrl::getOne(EntityNameTestFixtures::ENTITY_NAME_ONE_ID);
        $response = $client->get($url);
        $this->assertSame($expectedReturnStatus, $response->getStatusCode());

        $this->validateOpenApiResponse($response, new OperationAddress(
            path: $url,
            method: 'get',
        ));
    }

    public function testGetOneNotFound(): void
    {
        $client = $this->getApiClient(User::APP_ADMIN_ID);
        $url = EntityNameUrl::getOne(App::ZERO); // Non-existent ID
        $response = $client->get($url);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $this->validateOpenApiResponse($response, new OperationAddress(
            path: $url,
            method: 'get',
        ));
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[DataProvider('userAccessCreatedDataProvider')]
    public function testCreate(int $userId, int $expectedReturnStatus): void
    {
        $client = $this->getApiClient($userId);
        $url = EntityNameUrl::create();
        $payload = self::createValidPayload();
        $response = $client->post($url, jsonBody: $payload);
        $responseContent = $client->getResponseContent();
        $this->assertSame($expectedReturnStatus, $response->getStatusCode());

        $this->validateOpenApiResponse($response, new OperationAddress(
            path: $url,
            method: 'post',
        ));

        if (Response::HTTP_CREATED === $expectedReturnStatus) {
            $entityFromDb = $this->entityManager->find(EntityName::class, $responseContent['id']);
            $this->assertInstanceOf(EntityName::class, $entityFromDb);
            $this->assertEntitySame($responseContent, $entityFromDb);
        }
    }

    #[DataProvider('createCreateFailureDataProvider')]
    public function testCreateValidationFailure(array $requestJson, array $validationErrors): void
    {
        $client = $this->getApiClient(User::APP_ADMIN_ID);
        $url = EntityNameUrl::create();
        $response = $client->post($url, jsonBody: $requestJson);
        $responseContent = $client->getResponseContent();
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertValidationErrors($responseContent, $validationErrors);

        $this->validateOpenApiResponse($response, new OperationAddress(
            path: $url,
            method: 'post',
        ));
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[DataProvider('userAccessOkDataProvider')]
    public function testUpdate(int $userId, int $expectedReturnStatus): void
    {
        $client = $this->getApiClient($userId);
        $url = EntityNameUrl::update(EntityNameTestFixtures::ENTITY_NAME_ONE_ID);
        $payload = self::createValidPayload();
        $payload['id'] = EntityNameTestFixtures::ENTITY_NAME_ONE_ID;
        $payload['texts']['title'] = 'EntityName test title updated';
        $payload['texts']['description'] = 'EntityName test description updated';
        $response = $client->put($url, jsonBody: $payload);
        $responseContent = $client->getResponseContent();
        $this->assertSame($expectedReturnStatus, $response->getStatusCode());

        $this->validateOpenApiResponse($response, new OperationAddress(
            path: $url,
            method: 'put',
        ));

        if (Response::HTTP_OK === $expectedReturnStatus) {
            $entityFromDb = $this->entityManager->find(EntityName::class, $responseContent['id']);
            $this->assertInstanceOf(EntityName::class, $entityFromDb);
            $this->assertEntitySame($responseContent, $entityFromDb);
        }
    }

    #[DataProvider('userAccessNoContentDataProvider')]
    public function testDelete(int $userId, int $expectedReturnStatus): void
    {
        $client = $this->getApiClient($userId);
        $url = EntityNameUrl::delete(EntityNameTestFixtures::ENTITY_NAME_TO_DELETE_ID);
        $response = $client->delete($url);
        $this->assertSame($expectedReturnStatus, $response->getStatusCode());

        $this->validateOpenApiResponse($response, new OperationAddress(
            path: $url,
            method: 'delete',
        ));
    }

    public static function createCreateFailureDataProvider(): array
    {
        $payload = self::createValidPayload();

        $payloadBadTexts = $payload;
        $payloadBadTexts['texts']['title'] = App::EMPTY_STRING;

        $payloadBadRelations = $payload;
        $payloadBadRelations['site'] = App::ZERO;

        return [
            'Bad texts' => [
                'requestJson' => $payloadBadTexts,
                'validationErrors' => [
                    'texts.title' => [
                        ValidationException::ERROR_FIELD_LENGTH_MIN,
                    ],
                ],
            ],
            'Bad related entities' => [
                'requestJson' => $payloadBadRelations,
                'validationErrors' => [
                    'site' => [
                        ValidationException::ERROR_FIELD_EMPTY,
                    ],
                ],
            ],
        ];
    }

    private static function createValidPayload(): array
    {
        return [
            'texts' => [
                'title' => 'EntityName test title',
                'description' => 'EntityName test description',
            ],
            'site' => SiteFixtures::SME_SITE_ID,
            'siteGroup' => SiteGroupFixtures::SME_FAMILY_ID,
        ];
    }

    private function assertEntitySame(array $data, EntityName $entity): void
    {
        $this->assertSame($data['texts']['title'], $entity->getTexts()->getTitle());
        $this->assertSame($data['texts']['description'], $entity->getTexts()->getDescription());
        $this->assertSame($data['site'], $entity->getSite()->getId());
        $this->assertSame($data['siteGroup'], $entity->getSiteGroup()->getId());
    }
}
```

## Rule: Complete Implementation Checklist

### Files to Create (in order):
1. **Directory Structure**: Create `src/Domain/EntityName/Entity/`, `src/Domain/EntityName/Repository/`, `src/Domain/EntityName/Manager/`, `src/Domain/EntityName/Facade/`, `src/Domain/EntityName/Controller/Api/Adm/V1/`
2. **Texts Embeddable** (if needed): `src/Domain/EntityName/Entity/Embeds/EntityNameTexts.php`
3. **Entity**: `src/Domain/EntityName/Entity/EntityName.php`
4. **Repository**: `src/Domain/EntityName/Repository/EntityNameRepository.php`
5. **Manager**: `src/Domain/EntityName/Manager/EntityNameManager.php`
6. **Facade**: `src/Domain/EntityName/Facade/EntityNameFacade.php`
7. **Controller**: `src/Domain/EntityName/Controller/Api/Adm/V1/EntityNameController.php`
8. **Test Fixtures**: `tests/data/Fixtures/EntityNameTestFixtures.php`
9. **URL Factory**: `tests/data/UrlFactory/Api/Adm/EntityNameUrl.php`
10. **Controller Test**: `tests/Domain/EntityName/Controller/Adm/V1/EntityNameControllerTest.php`

### Files to Modify:
11. **Permissions**: Add constants to `src/Security/Permission.php`
12. **Permission Config**: Add section to `config/packages/anzu_systems_common_permissions.yaml`

### Database Migration:
13. **Generate Migration**: `bin/cmd bin/console doctrine:migrations:diff`
14. **Run Migration**: `bin/cmd bin/console doctrine:migrations:migrate`
