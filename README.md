# rentbetter/phpstan-rules

PHPStan rules for Symfony/Doctrine projects, extracted from [RentBetter](https://rentbetter.com.au) coding conventions.

PHPStan rules covering Doctrine usage, Symfony routing, architecture patterns, JSON serialization, and enum enforcement.

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
| `EntityTablePrefixRule` | `rentbetter.entityTablePrefix` | Entities missing `#[ORM\Table(name: 'tbl_...')]` — prefix makes direct table usage searchable |
| `EntityDeferredExplicitRule` | `rentbetter.entityDeferredExplicit` | Entities missing `#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]` — prevents accidental flushes, improves performance, and gives control over batch saving and flush timing |
| `RedundantColumnTypeRule` | `rentbetter.redundantColumnType` | `type: Types::STRING` on `string` properties etc. — Doctrine infers these from the PHP type, so specifying them is noise |
| `NoHardcodedValueInQueryRule` | `rentbetter.noHardcodedValueInQuery` | Hardcoded numeric/string values in DQL queries — use bound parameters or `$enum->value` |

### Symfony Routes

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `NoGenericIdParameterRule` | `rentbetter.noGenericId` | Route methods with param named `$id` (use `$tenancyId` etc.) |
| `RouteRequiresMethodRule` | `rentbetter.routeRequiresMethod` | `#[Route]` missing `methods:` parameter |
| `RouteRequiresUuidRequirementRule` | `rentbetter.routeRequiresUuidRequirement` | Route `{fooId}` params without `requirements:` constraint |
| `ActionMethodNamingRule` | `rentbetter.actionMethodNaming` | Public route methods not ending in `Action` |
| `RouteNameMatchesMethodRule` | `rentbetter.routeNameMatchesMethod` | Route `name:` doesn't match method minus `Action` suffix |
| `NoClassLevelRouteRule` | `rentbetter.noClassLevelRoute` | `#[Route]` on controller classes — class-level prefixes hide the real path |
| `RoutePathCamelCaseRule` | `rentbetter.routePathCamelCase` | Route path segments using `snake_case` instead of `camelCase` |
| `RouteIdParamMustBeStringRule` | `rentbetter.routeIdParamMustBeString` | Route ID parameters not typed as `string` |
| `RouteMethodSignatureRule` | `rentbetter.routeMethodSignature` | Route methods missing `ApiRequest` first param or `ApiResponse` return type |
| `RouteRequiresSpecApiRule` | `rentbetter.routeRequiresSpecApi` | Route methods missing `#[Spec\Api]` attribute |
| `NoGetPayloadInControllerRule` | `rentbetter.noGetPayloadInController` | Controllers calling `getJsonPayload()`/`getPayload()` directly |
| `NoMultipleRequestParamsInControllerRule` | `rentbetter.noMultipleRequestParamsInController` | Controllers accessing 2+ request parameters — use a form type |
| `NoRequestGetContentInControllerRule` | `rentbetter.noRequestGetContentInController` | Controllers calling `Request::getContent()` directly |
| `NoEntityAsFormDataClassRule` | `rentbetter.noEntityAsFormDataClass` | Form types using an entity as `data_class` instead of a DTO |

### Architecture

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `ReadonlyServiceRule` | `rentbetter.readonlyService` | Non-readonly service classes |
| `NamedArgumentForBooleanRule` | `rentbetter.namedArgumentForBoolean` | Boolean literals passed positionally to project methods |
| `SaveParameterDefaultRule` | `rentbetter.saveParameterDefault` | `$save`/`$flush` bool params defaulting to `false` instead of `true` |
| `MoneyReturnTypeRule` | `rentbetter.moneyReturnType` | Public methods returning `MoneyModelV2` instead of `MoneyInterface` |
| `UseDateFormatterRule` | `rentbetter.useDateFormatter` | Direct `->format()` on DateTime objects instead of `DateFormatter` |

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

### Level-aware rules

By default, all enabled rules fire regardless of the PHPStan analysis level. To
suppress custom rules at lower levels, set `ruleLevel` to the desired threshold:

```neon
parameters:
    rentbetter:
        ruleLevel: 5   # only architecture rules (level 5+) will fire
```

Each rule has a minimum level threshold:

| Level | Category | Rules |
|-------|----------|-------|
| **5** | Architecture | `noEntityManagerInController`, `noRepositoryInController`, `noDirectFlush`, `noRequestGetContentInController`, `noGetPayloadInController`, `noMultipleRequestParamsInController`, `noClassLevelRoute`, `noEntityAsFormDataClass` |
| **6** | Correctness | `noPublicCollectionReturn`, `statusColumnMustBeEnum`, `noHardcodedValueInQuery`, `entityDeferredExplicit`, `entityTablePrefix`, `redundantColumnType`, `routeRequiresMethod`, `routeMethodSignature`, `routeIdParamMustBeString`, `routeRequiresUuidRequirement`, `moneyReturnType`, `saveParameterDefault`, `useDateFormatter` |
| **8** | Convention | `noGenericId`, `actionMethodNaming`, `routeNameMatchesMethod`, `routePathCamelCase`, `routeRequiresSpecApi`, `readonlyService`, `namedArgumentForBoolean`, `noSnakeCaseJsonKey`, `noNullInJsonSerialize` |

When `ruleLevel` is `null` (the default), all rules fire — backward compatible.

**Note:** PHPStan's `--level` CLI flag does not update the `%level%` parameter, so
`ruleLevel` must be set to a fixed integer, not `%level%`.

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
