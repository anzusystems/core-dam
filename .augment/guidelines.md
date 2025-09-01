# Augment Agent Guidelines for ANZU Core-CMS

## 📚 MANDATORY RULES - ALWAYS FOLLOW

### Code Quality and Development Standards
**CRITICAL**: Always follow these comprehensive rule sets for ALL code generation and modifications:

- **[Code Quality Standards](/.augment/rules/code-quality-standards.md)** - MUST be applied to every piece of code generated or modified
- **[Entity CRUD Creation Rules](/.augment/rules/entity-crud-creation.md)** - Complete implementation guide for creating entities with CRUD operations

## 🚨 CRITICAL EXECUTION RULES - ALWAYS FOLLOW

### 1. Script Execution
Use the appropriate wrapper for each command:

```bash
# ✅ CORRECT
bin/cmd composer install          # Package management
bin/cmd bin/console cache:clear   # Symfony console
bin/cc                           # Cache clearing (direct wrapper)
bin/ecs                          # Code style (direct wrapper)
bin/phpstan                      # Static analysis (direct wrapper)
bin/test                         # Tests (direct wrapper)
bin/test tests/SomeTest.php      # Specific test file

# ❌ WRONG - Never execute directly without wrappers
composer install
./bin/console cache:clear
vendor/bin/ecs
```

### 2. Quality Tools (ALL OPTIONAL - only run when requested)
1. **Code style**: `bin/ecs` (only when user asks)
2. **Static analysis**: `bin/phpstan` (only when user asks)
3. **Tests**: `bin/test` (only when user asks)
4. **Cache clearing**: `bin/cc` (only when user asks)

### 3. Execution Policy
- **NEVER run quality tools automatically** after code changes
- **ONLY run when user explicitly requests** (e.g., "run ecs", "check code style", "run tests")
- These tools can be time-consuming and should be user-initiated

## 📋 Common Commands

| Task | Command |
|------|---------|
| Install packages | `bin/cmd composer require <package>` |
| Remove packages | `bin/cmd composer remove <package>` |
| Clear cache | `bin/cc` (when requested) |
| Run migrations | `bin/cmd bin/console doctrine:migrations:migrate` |
| Code style | `bin/ecs` (when requested) |
| Static analysis | `bin/phpstan` (when requested) |
| All tests | `bin/test` (when requested) |
| Specific test | `bin/test tests/SomeTest.php` (when requested) |
| Debug routes | `bin/cmd bin/console debug:router` |

## 🚫 What NOT to Do

- Never execute commands without proper wrappers
- Never modify `composer.lock`, `symfony.lock`, `var/cache/*`, `vendor/*`
- Never run quality tools (`bin/ecs`, `bin/phpstan`, `bin/test`) automatically after code changes
- Only run quality tools when user explicitly requests them
- do not use `else` statement
- do not use negation `!` after bracket `(`, for instance use `if (false === empty(...`

## What to Do

- when you need current datetime, use `App::getAppDate()`
- instead of `0` use `App::ZERO`
- instead of `''` use `App::EMPTY_STRING`
- all sql queries must be located in entity repository

## 🎯 When to Run Quality Tools

**Run these ONLY when user explicitly asks:**
- "run ecs" or "check code style" → `bin/ecs`
- "run phpstan" or "check static analysis" → `bin/phpstan`
- "run tests" or "test this" → `bin/test`
- "clear cache" or "cache clear" → `bin/cc`

**DO NOT run automatically** after code changes.

## 🧪 Test Environment Configuration

### Test Data Management
When user mentions data handling preferences during testing, update `.env.test.local`:

**When user says "don't drop data during test":**
```bash
ANZU_TEST_CLEAR_CACHE=false
ANZU_TEST_REINDEX_DATA=false
ANZU_TEST_RECREATE_DB=false
ANZU_TEST_REGENERATE_OPENAPI=false
```

**When user says "recreate data":**
```bash
ANZU_TEST_CLEAR_CACHE=true
ANZU_TEST_REINDEX_DATA=true
ANZU_TEST_RECREATE_DB=true
ANZU_TEST_REGENERATE_OPENAPI=true
```

These settings control whether test data is preserved or recreated between test runs.

## 📖 Rule Compliance and Quality Assurance

### Mandatory Rule Following
- **ALL code generation** must comply with the code quality standards
- **Entity creation** must follow the complete CRUD creation workflow
- **No exceptions** - these rules apply to every task, regardless of size or complexity

### Before Code Generation
1. Review relevant rules in `/.augment/rules/` directory
2. Understand the established patterns and conventions
3. Plan implementation following the documented standards

### After Code Generation
- Generated code should pass all automated quality checks
- Code should be production-ready and maintainable
- Follow established codebase patterns and conventions

### Quality Verification (when requested by user)
- Run `bin/ecs` for code style validation
- Run `bin/phpstan` for static analysis
- Run `bin/test` for functionality verification

**Remember**: Quality tools are only run when explicitly requested by the user, never automatically.
