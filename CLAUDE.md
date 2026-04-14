# CLAUDE.md

Anzu Core DAM — Symfony 7 / PHP 8.4 app for managing digital assets. Part of the Anzu microservice ecosystem.

## Common commands

All commands run in the Docker container via `bin/cmd`:

```bash
bin/cmd composer install
bin/cmd bin/console cache:clear
bin/cmd vendor/bin/phpunit                                   # all tests
bin/cmd vendor/bin/phpunit <path>                            # single file/method (use --filter)
bin/cmd vendor/bin/phpstan analyse -c phpstan.neon           # static analysis (level 5)
bin/cmd vendor/bin/ecs check [--fix]                         # code style
bin/cmd bin/console doctrine:migrations:diff
bin/cmd bin/console doctrine:migrations:migrate
docker compose up -d
```

## Code standards

- `final` classes unless inheritance is required; `declare(strict_types=1)` everywhere
- Use `App::EMPTY_STRING` instead of `''`
- PSR-12 + Slevomat (enforced via ECS); Yoda conditions; numeric separators (`2_000`)
- Forbidden: `dump()`, `var_dump()`, `dd()`, `echo`, `die`
- Imports grouped: PHP core → third-party → AnzuSystems → App (alphabetical)
- Properties: private → protected → public. Methods: public → protected → private
- 4-space indent, 120-char lines

## Further reading (read on demand, not preloaded)

- Architecture / layered domain structure (Controller → Facade → Manager → Repository): explore `src/Domain/*/`
- Entity CRUD creation template: `.augment/rules/entity-crud-creation.md`
- Routes: `config/routes/dam.php`
- Permissions: `src/Security/Permission.php` + `config/packages/anzu_systems_common_permissions.yaml`

## Anzu ecosystem paths

Sibling checkouts under a shared projects root (`$PROJECTS_ROOT`, e.g. `~/projects`):

| Type | Path |
|------|------|
| Symfony apps | `$PROJECTS_ROOT/petit/{app-name}/` |
| Bundles (editable source) | `$PROJECTS_ROOT/anzusystems/{bundle-name}/` |
| Vue admin frontends | `$PROJECTS_ROOT/anzusystems/{admin-name}/` |
| Common admin lib | `$PROJECTS_ROOT/anzusystems/common-admin/` (shared TS types/components) |

**Bundle editing rule**: bundle source dirs are Docker-volume-mounted into `vendor/anzusystems/{bundle-name}/`. Always edit at the source path, never in `vendor/`.
