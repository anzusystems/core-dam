# Code Quality Standards

## Overview
These code quality standards MUST be followed by Augment for ALL code generation and modifications in the Anzu CMS system. These are not optional guidelines but mandatory requirements.

## Rule: ALWAYS ENFORCED - Core Code Quality Standards

**CRITICAL: Augment MUST always apply these standards to every piece of code generated or modified, regardless of the specific task or domain.**

### MUST Requirements for All PHP Code:

#### Class Declarations:
- All classes MUST be declared as `final` unless inheritance is specifically required
- All classes MUST use `declare(strict_types=1)` at the top of the file
- All classes MUST have proper namespace declarations
- All classes MUST follow PSR-12 coding standards

#### Type Declarations:
- All properties MUST have proper type declarations
- All method parameters MUST have type declarations
- All methods MUST have return type declarations
- Use union types when appropriate (e.g., `string|null` instead of `?string` when beneficial)

#### String Handling:
- Use `App::EMPTY_STRING` instead of empty string literals (`''`)
- Use `App::EMPTY_ANZUTAP_BODY` for empty TipTap content arrays
- Use appropriate App constants for other common empty values

#### Import Organization:
- Group imports logically in this order:
  1. PHP core/framework imports
  2. Third-party library imports  
  3. AnzuSystems imports
  4. Application imports (App\...)
- Sort imports alphabetically within each group
- Use fully qualified class names in imports
- Avoid unused imports

#### Class Structure Order:
1. Class constants
2. Properties (private first, then protected, then public)
3. Constructor
4. Public methods
5. Protected methods
6. Private methods

#### Naming Conventions:
- Class names: PascalCase (e.g., `TestEntity`, `UserManager`)
- Method names: camelCase (e.g., `getUserById`, `createNewEntity`)
- Property names: camelCase (e.g., `$userName`, `$createdAt`)
- Constants: SCREAMING_SNAKE_CASE (e.g., `MAX_ITEMS`, `DEFAULT_STATUS`)
- Database table names: snake_case (handled by Doctrine)

#### Validation and Serialization:
- Always use proper validation constraints from `AnzuSystems\CommonBundle\Validator\Constraints` and `Symfony\Component\Validator\Constraints`
- Use `#[Serialize]` attributes for API serialization
- Use `EntityIdHandler` for entity relationships in serialization
- Include proper validation messages using `ValidationException` constants

#### Documentation:
- Include PHPDoc comments for complex methods
- Document method parameters and return types when not obvious
- Add class-level documentation for complex entities
- Include `@throws` annotations for methods that can throw exceptions

### Code Structure Standards:

#### Indentation and Formatting:
- Use 4 spaces for indentation (no tabs)
- Maximum line length of 120 characters
- Use consistent spacing around operators
- Align array elements and method parameters when spanning multiple lines

#### Method Design:
- Keep methods focused and single-purpose
- Avoid methods longer than 50 lines
- Use meaningful parameter and variable names
- Return early to reduce nesting levels

#### Error Handling:
- Use typed exceptions where appropriate
- Include meaningful error messages
- Follow the established exception handling patterns in the codebase

## Rule: Entity-Specific Standards

When working with entities, additionally apply:

#### Interface Implementation:
- Always implement required interfaces (`IdentifiableInterface`, `UserTrackingInterface`, etc.)
- Use corresponding traits for common functionality
- Implement `SiteAwareInterface` for site-aware entities

#### Relationship Handling:
- Use proper Doctrine annotations for relationships
- Include validation constraints (`#[AnzuAssert\NotEmptyId]`)
- Use `EntityIdHandler` for serialization of relationships
- Initialize relationships in constructors

#### Embedded Objects:
- Create separate embeddable classes for complex value objects
- Use proper validation on embedded objects (`#[Assert\Valid]`)
- Follow naming conventions for embedded classes (e.g., `EntityNameTexts`)

## Rule: Repository and Service Standards

#### Repository Classes:
- Extend `AbstractAnzuRepository`
- Implement `getEntityClass()` method
- Use proper generic type annotations (`@extends AbstractAnzuRepository<EntityName>`)

#### Manager Classes:
- Extend `AbstractManager`
- Use `trackCreation()` and `trackModification()` for audit trails
- Use `$this->flush($flush)` for database operations
- Include proper method signatures with type hints

#### Facade Classes:
- Declare as `final readonly class`
- Inject dependencies via constructor
- Include proper exception handling (`@throws ValidationException`)
- Use validator for entity validation
