# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Anzu Core DAM — a Symfony 7.0 application for managing digital assets (images, audio, video, documents). It is part of the Anzu CMS microservice ecosystem. PHP 8.4+ required.

Key dependencies: `anzusystems/core-dam-bundle` (provides most DAM domain logic), `anzusystems/auth-bundle`, `anzusystems/common-bundle`, `anzusystems/serializer-bundle`.

## Common Commands

All commands run inside the Docker container via `bin/cmd`:

```bash
# Install dependencies
bin/cmd composer install

# Clear cache
bin/cmd bin/console cache:clear

# Run all tests
bin/cmd vendor/bin/phpunit

# Run a single test file
bin/cmd vendor/bin/phpunit tests/Controller/Api/Pub/PodcastControllerTest.php

# Run a single test method
bin/cmd vendor/bin/phpunit --filter testGetOne tests/Controller/Api/Pub/PodcastControllerTest.php

# Static analysis (PHPStan level 5)
bin/cmd vendor/bin/phpstan analyse -c phpstan.neon

# Code style check
bin/cmd vendor/bin/ecs check

# Code style fix
bin/cmd vendor/bin/ecs check --fix

# Database migrations
bin/cmd bin/console doctrine:migrations:diff
bin/cmd bin/console doctrine:migrations:migrate

# Start docker containers
docker compose up -d
```

## Architecture

### Layered Domain Structure

Business logic lives in `src/Domain/{DomainName}/` following a strict layered pattern:

```
src/Domain/{DomainName}/
├── Controller/Api/Adm/V1/   # Admin API endpoints (versioned)
├── Entity/                   # Doctrine entities
│   └── Embeds/              # Embeddable value objects
├── Facade/                   # Orchestrates validation + manager calls
├── Manager/                  # Entity lifecycle (create/update/delete with audit tracking)
└── Repository/              # Data access, extends AbstractAnzuRepository
```

**Request flow**: Controller → Facade → Manager → Repository

- **Controllers** extend `AbstractApiPubController` (public) or use route-level auth. They call `denyAccessUnlessGranted()` with `Permission::` constants, call `App::throwOnReadOnlyMode()` for writes, and set audit log resources.
- **Facades** are `final readonly class`. They validate via `Validator` then delegate to managers. Methods declare `@throws ValidationException`.
- **Managers** extend `AbstractManager`. They call `trackCreation()`/`trackModification()` for audit trails and use `$this->flush($flush)`.
- **Repositories** extend `AbstractAnzuRepository` and implement `getEntityClass()`.

### API Route Structure

Routes are versioned and scoped by audience (defined in `config/routes/dam.php`):

- `/api/adm/v1/` — Admin endpoints (most CRUD lives here, both from bundle and app)
- `/api/sys/v1/` — System-to-system endpoints
- `/api/pub/v1/` — Public endpoints (from bundle)
- `/api/pub/` — Public endpoints (app-level)
- `/api/ugc/v1/` and `/api/ugc/vlegacy/` — User-generated content endpoints

Most DAM domain controllers come from `@AnzuSystemsCoreDamBundle`. App-level controllers are in `src/Controller/`.

### Data Stores

- **MySQL 8.0** (primary, via Doctrine ORM) — multiple connections: `default` (core-dam), `media_api`, `dam_media_api_mig`
- **Redis** — caching and sessions
- **MongoDB** — app logs and audit trails
- **Elasticsearch** — full-text search indexing
- **Google Cloud Storage** — asset file storage (buckets per asset type)

### Async Processing

Symfony Messenger with Google Pub/Sub transport. Message handlers in `src/Messenger/Handler/`. Messages in `src/Messenger/Message/`.

### Entity Conventions

Entities must implement `IdentifiableInterface`, `UserTrackingInterface`, `TimeTrackingInterface` with corresponding traits. Site-aware entities additionally implement `SiteAwareInterface` with `SiteMatchesGroup` constraint.

Embedded value objects (e.g., `EntityNameTexts`) go in `Entity/Embeds/` and are annotated with `#[ORM\Embeddable]`.

### Permissions

Permission constants are defined in `src/Security/Permission.php` following `CMS_ENTITYNAME_ACTION` pattern. Permission configuration in `config/packages/anzu_systems_common_permissions.yaml`.

### Serialization

Uses `anzusystems/serializer-bundle` with `#[Serialize]` attributes. Entity relationships use `EntityIdHandler` for serialization.

### OpenAPI Documentation

All API endpoints require OpenAPI attributes (`OAResponse`, `OARequest`, `OAParameterPath`, etc.) from `AnzuSystems\CommonBundle\Model\OpenApi\`. Four documentation areas: default, adm, sys, pub.

## Code Standards

- All classes `final` unless inheritance is required
- `declare(strict_types=1)` in every file
- Use `App::EMPTY_STRING` instead of `''`
- PSR-12 with Slevomat extensions (enforced via ECS)
- Yoda conditions enforced (`=== $value` not `$value ===`)
- Numeric literal separators required (e.g., `2_000` not `2000`)
- Forbidden: `dump()`, `var_dump()`, `dd()`, `echo`, `die`
- Imports sorted alphabetically, grouped: PHP core → third-party → AnzuSystems → App
- Properties ordered: private → protected → public
- Methods ordered: public → protected → private
- 4-space indentation, 120-char max line length

## Testing

PHPUnit 12.0 with tests in `tests/`. Test structure mirrors the source:

- `tests/Controller/Api/` — API controller tests (extend `AbstractApiController` → `AbstractController` → `WebTestCase`)
- `tests/Domain/` — Domain-specific tests
- `tests/data/Fixtures/` — Test fixtures (extend `AbstractTestFixtures<Entity>`)
- `tests/data/UrlFactory/` — URL helper factories for test HTTP calls
- `tests/data/Model/` — Test models

Tests use database transactions (auto-rollback in `tearDown`). Controller tests validate responses against OpenAPI specs using `League\OpenAPIValidation`. Data providers test permission-based access for different user roles.

## Creating a New CRUD Entity

Follow the template in `.augment/rules/entity-crud-creation.md`. Required files:

1. Entity (`src/Domain/{Name}/Entity/{Name}.php`)
2. Embeds if needed (`src/Domain/{Name}/Entity/Embeds/{Name}Texts.php`)
3. Repository (`src/Domain/{Name}/Repository/{Name}Repository.php`)
4. Manager (`src/Domain/{Name}/Manager/{Name}Manager.php`)
5. Facade (`src/Domain/{Name}/Facade/{Name}Facade.php`)
6. Controller (`src/Domain/{Name}/Controller/Api/Adm/V1/{Name}Controller.php`)
7. Add permissions to `src/Security/Permission.php` and `config/packages/anzu_systems_common_permissions.yaml`
8. Test fixtures (`tests/data/Fixtures/{Name}TestFixtures.php`)
9. URL factory (`tests/data/UrlFactory/Api/Adm/{Name}Url.php`)
10. Controller test (`tests/Controller/Api/Adm/V1/{Name}ControllerTest.php` or matching domain path)
11. Generate and run migration

## Anzu Ecosystem & Related Projects

This project is part of the Anzu microservice ecosystem. Multiple projects share bundles and libraries.

### Path Conventions
| Type | Host path pattern | This project's instances |
|------|-------------------|------------------------|
| Symfony apps | `/home/tomas/Projects/anzu/{app-name}/` | `core-dam` |
| Bundles (source, editable) | `/home/tomas/Projects/anzusystems/{bundle-name}/` | `core-dam-bundle`, `common-bundle` |
| Vue admin frontends | `/home/tomas/Projects/anzusystems/{admin-name}/` | `admin-dam` |
| Common admin lib | `/home/tomas/Projects/anzusystems/common-admin/` | Shared TS types/components |

### Bundle Editing Rule
Bundle source directories are Docker volume-mounted into `vendor/anzusystems/{bundle-name}/` (configured in `docker-compose.override.yml`). **Always edit at the source path (`/home/tomas/Projects/anzusystems/{bundle-name}/`), never in `vendor/`.**
