# rentbetter/phpstan-rules

PHPStan rules for Symfony/Doctrine projects, extracted from [RentBetter](https://rentbetter.com.au) coding conventions.

14 rules covering Doctrine usage, Symfony routing, architecture patterns, JSON serialization, and enum enforcement.

## Installation

```bash
composer require --dev rentbetter/phpstan-rules
```

The rules are auto-discovered via PHPStan's extension mechanism — no manual `includes` needed.

## Rules

### Doctrine

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `NoPublicCollectionReturnRule` | `rentbetter.noCollectionReturn` | Entity public methods returning `Collection` instead of `array` |
| `NoEntityManagerInControllerRule` | `rentbetter.noEntityManagerInController` | Controller constructors injecting `EntityManagerInterface` |
| `NoRepositoryInControllerRule` | `rentbetter.noRepositoryInController` | Controller constructors injecting `*Repository` classes |
| `NoDirectFlushRule` | `rentbetter.noDirectFlush` | Calling `->flush()` on `EntityManagerInterface` |

### Symfony Routes

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `NoGenericIdParameterRule` | `rentbetter.noGenericId` | Route methods with param named `$id` (use `$tenancyId` etc.) |
| `RouteRequiresMethodRule` | `rentbetter.routeRequiresMethod` | `#[Route]` missing `methods:` parameter |
| `RouteRequiresUuidRequirementRule` | `rentbetter.routeRequiresUuidRequirement` | Route `{fooId}` params without `requirements:` constraint |
| `ActionMethodNamingRule` | `rentbetter.actionMethodNaming` | Public route methods not ending in `Action` |
| `RouteNameMatchesMethodRule` | `rentbetter.routeNameMatchesMethod` | Route `name:` doesn't match method minus `Action` suffix |

### Architecture

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `ReadonlyServiceRule` | `rentbetter.readonlyService` | Non-readonly service classes |
| `NamedArgumentForBooleanRule` | `rentbetter.namedArgumentForBoolean` | Boolean literals passed positionally to project methods |

### Serialization

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `NoSnakeCaseJsonKeyRule` | `rentbetter.noSnakeCaseJsonKey` | `jsonSerialize()` returning arrays with `snake_case` keys |
| `NoNullInJsonSerializeRule` | `rentbetter.noNullInJsonSerialize` | `jsonSerialize()` returning raw arrays without null filtering |

### Enum

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `StatusColumnMustBeEnumRule` | `rentbetter.statusColumnMustBeEnum` | Doctrine `#[Column]` on `*status*` properties without `enumType:` |

## Configuration

All rules are enabled by default. Disable individual rules in your `phpstan.neon`:

```neon
parameters:
    rentbetter:
        readonlyService: false
        noDirectFlush: false
```

### Configurable parameters

```neon
parameters:
    rentbetter:
        # Which namespace segment identifies entity classes (default: Entity)
        entityNamespaceSegment: Entity

        # Namespace prefixes for ReadonlyServiceRule (default: [App\])
        serviceNamespaceIncludes:
            - App\

        # Class name patterns to exclude from ReadonlyServiceRule
        serviceExcludePatterns:
            - Controller
            - Command
            - Entity
            - Migration

        # Namespace prefixes for NamedArgumentForBooleanRule (default: [App\])
        projectNamespaces:
            - App\

        # Function name for NoNullInJsonSerializeRule (default: array_filter_nulls)
        nullFilterFunction: array_filter_nulls
```

## Examples

### Before

```php
// rentbetter.noEntityManagerInController
class UserController {
    public function __construct(
        private EntityManagerInterface $em,
    ) {}
}

// rentbetter.noGenericId
#[Route('/users/{id}', methods: 'GET')]
public function getUserAction(string $id) {}

// rentbetter.routeRequiresMethod
#[Route('/users')]
public function listUsersAction() {}

// rentbetter.namedArgumentForBoolean
$service->save($entity, true);

// rentbetter.noSnakeCaseJsonKey
public function jsonSerialize(): array {
    return ['first_name' => $this->firstName];
}
```

### After

```php
// Inject a service, not the EntityManager
class UserController {
    public function __construct(
        private UserService $userService,
    ) {}
}

// Use descriptive parameter names
#[Route('/users/{userId}', methods: 'GET', requirements: ['userId' => Uuid::REGEX])]
public function getUserAction(string $userId) {}

// Always specify HTTP methods
#[Route('/users', methods: 'GET')]
public function listUsersAction() {}

// Use named arguments for booleans
$service->save($entity, flush: true);

// Use camelCase for JSON keys
public function jsonSerialize(): array {
    return array_filter_nulls(['firstName' => $this->firstName]);
}
```

## Requirements

- PHP >= 8.4
- PHPStan >= 2.0

## License

MIT - see [LICENSE](LICENSE).
