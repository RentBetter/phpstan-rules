# ptgs/phpstan-rules

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
| `NoPublicCollectionReturnRule` | `ptgs.noCollectionReturn` | Entity public methods returning `Collection` instead of `array` |
| `NoDirectFlushRule` | `ptgs.noDirectFlush` | Calling `->flush()` on `EntityManagerInterface` |
| `EntityTablePrefixRule` | `ptgs.entityTablePrefix` | Entities missing `#[ORM\Table(name: 'tbl_...')]` — prefix makes direct table usage searchable |
| `EntityDeferredExplicitRule` | `ptgs.entityDeferredExplicit` | Entities missing `#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]` — prevents accidental flushes, improves performance, and gives control over batch saving and flush timing |
| `RedundantColumnTypeRule` | `ptgs.redundantColumnType` | `type: Types::STRING` on `string` properties etc. — Doctrine infers these from the PHP type, so specifying them is noise |
| `NoHardcodedValueInQueryRule` | `ptgs.noHardcodedValueInQuery` | Hardcoded numeric/string values in DQL queries — use bound parameters or `$enum->value` |

### Symfony Routes

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `NoGenericIdParameterRule` | `ptgs.noGenericId` | Route methods with param named `$id` (use `$tenancyId` etc.) |
| `RouteRequiresMethodRule` | `ptgs.routeRequiresMethod` | `#[Route]` missing `methods:` parameter |
| `RouteRequiresUuidRequirementRule` | `ptgs.routeRequiresUuidRequirement` | Route `{fooId}` params without `requirements:` constraint |
| `ActionMethodNamingRule` | `ptgs.actionMethodNaming` | Public route methods not ending in `Action` |
| `RouteNameMatchesMethodRule` | `ptgs.routeNameMatchesMethod` | Route `name:` doesn't match method minus `Action` suffix |
| `NoClassLevelRouteRule` | `ptgs.noClassLevelRoute` | `#[Route]` on controller classes — class-level prefixes hide the real path |
| `RoutePathCamelCaseRule` | `ptgs.routePathCamelCase` | Route path segments using `snake_case` or `kebab-case` instead of `camelCase` |
| `RouteIdParamMustBeStringRule` | `ptgs.routeIdParamMustBeString` | Route ID parameters not typed as `string` |
| `RouteMethodSignatureRule` | `ptgs.routeMethodSignature` | Route methods missing `ApiRequest` first param or `ApiResponse` return type |
| `RouteRequiresSpecApiRule` | `ptgs.routeRequiresSpecApi` | Route methods missing `#[Spec\Api]` attribute |
| `NoJsonDecodeInControllerRule` | `ptgs.noJsonDecodeInController` | Controllers calling `json_decode()` — hand-parsing JSON; use the Form component |
| `NoMultipleRequestParamsInControllerRule` | `ptgs.noMultipleRequestParamsInController` | Controllers accessing 2+ request parameters — use a form type |
| `NoRequestPayloadAccessRule` | `ptgs.noRequestPayloadAccess` | Any call to `Request::toArray()`, `Request::getPayload()`, or `Request::getContent()` — use the Form component to bind a typed DTO |
| `NoEntityAsFormDataClassRule` | `ptgs.noEntityAsFormDataClass` | Form types using an entity as `data_class` instead of a DTO |

### Architecture

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `ForbiddenDependencyRule` | `ptgs.forbiddenDependency` | Constructor params that violate a configured group→group dependency boundary (see [Dependency Boundaries](#dependency-boundaries)) |
| `ReadonlyServiceRule` | `ptgs.readonlyService` | Non-readonly service classes |
| `NamedArgumentForBooleanRule` | `ptgs.namedArgumentForBoolean` | Boolean literals passed positionally to project methods |
| `SaveParameterDefaultRule` | `ptgs.saveParameterDefault` | `$save`/`$flush` bool params defaulting to `false` instead of `true` |
| `MoneyReturnTypeRule` | `ptgs.moneyReturnType` | Public methods returning `MoneyModelV2` instead of `MoneyInterface` |
| `UseDateFormatterRule` | `ptgs.useDateFormatter` | Direct `->format()` on DateTime objects instead of `DateFormatter` |

### Serialization

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `NoSnakeCaseJsonKeyRule` | `ptgs.noSnakeCaseJsonKey` | `jsonSerialize()` returning arrays with `snake_case` keys |
| `NoNullInJsonSerializeRule` | `ptgs.noNullInJsonSerialize` | `jsonSerialize()` returning raw arrays without null filtering |

### Enum

| Rule | Error ID | What it detects |
|------|----------|----------------|
| `StatusColumnMustBeEnumRule` | `ptgs.statusColumnMustBeEnum` | Doctrine `#[Column]` on `*status*` properties without `enumType:` |

## Configuration

All rules are enabled by default. Disable individual rules in your `phpstan.neon`:

```neon
parameters:
    ptgs:
        readonlyService: false
        noDirectFlush: false
```

### Level-aware rules

By default, all enabled rules fire regardless of the PHPStan analysis level. To
suppress custom rules at lower levels, set `ruleLevel` to the desired threshold:

```neon
parameters:
    ptgs:
        ruleLevel: 5   # only architecture rules (level 5+) will fire
```

Each rule has a minimum level threshold:

| Level | Category | Rules |
|-------|----------|-------|
| **5** | Architecture | `forbiddenDependency`, `noDirectFlush`, `noRequestPayloadAccess`, `noJsonDecodeInController`, `noMultipleRequestParamsInController`, `noClassLevelRoute`, `noEntityAsFormDataClass` |
| **6** | Correctness | `noPublicCollectionReturn`, `statusColumnMustBeEnum`, `noHardcodedValueInQuery`, `entityDeferredExplicit`, `entityTablePrefix`, `redundantColumnType`, `routeRequiresMethod`, `routeMethodSignature`, `routeIdParamMustBeString`, `routeRequiresUuidRequirement`, `moneyReturnType`, `saveParameterDefault`, `useDateFormatter` |
| **8** | Convention | `noGenericId`, `actionMethodNaming`, `routeNameMatchesMethod`, `routePathCamelCase`, `routeRequiresSpecApi`, `readonlyService`, `namedArgumentForBoolean`, `noSnakeCaseJsonKey`, `noNullInJsonSerialize` |

When `ruleLevel` is `null` (the default), all rules fire — backward compatible.

**Note:** PHPStan's `--level` CLI flag does not update the `%level%` parameter, so
`ruleLevel` must be set to a fixed integer, not `%level%`.

### Namespace Groups

Several rules ask "is this class a controller? a service? an entity?". That's
answered centrally via the `groups` parameter. Each group is a list of patterns;
a class belongs to a group if its FQCN matches any of them.

Pattern syntax:

- A regex bracketed by `~` delimiters — string-matched against the FQCN
  (e.g. `~\\Service\\~` matches anything in a `\Service\` namespace segment).
  Using `~` lets `/` appear unescaped if you need it.
- Anything else — a literal FQCN, exact match.

Defaults shipped in `extension.neon`:

```neon
parameters:
    ptgs:
        groups:
            controller: ['~\\Controller\\~']
            service:    ['~\\Services?\\~', '~\\Helpers?\\~']
            repository: ['~\\Repository\\~']
            entity:     ['~\\Entity\\~']
            command:    ['~\\Command\\~']
            migration:  ['~\\Migrations?\\~']
            project:    ['~^App\\\\~', '~^PTGS\\\\~']
            dbAccess:
                - 'Doctrine\ORM\EntityManagerInterface'
                - 'Doctrine\DBAL\Connection'
                - 'PDO'
```

Override any group in your `phpstan.neon` to fit your project's namespace layout
(NEON replaces the entry rather than merging, so include the full list):

```neon
parameters:
    ptgs:
        groups:
            project: ['~^Acme\\\\~']
```

### Dependency Boundaries

`ForbiddenDependencyRule` enforces that classes in one group must not inject
constructor dependencies from another. Configure it as a map of from-group →
list of `{group, reason}`. The reason is included verbatim in the error message
so violators understand the *why*, not just the *what*.

Defaults:

```neon
parameters:
    ptgs:
        forbiddenDependencies:
            controller:
                - group: dbAccess
                  reason: 'Controllers must remain thin — push DB access into a service.'
                - group: repository
                  reason: 'Controllers should depend on services, not repositories directly.'
            service:
                - group: dbAccess
                  reason: 'Services must use repositories — repositories own all DB access.'
```

Example error:

```
Service PTGS\PropertyApi\Portal\Help\Service\HelpArticleSyncService may NOT depend on Doctrine\DBAL\Connection (group: dbAccess). Services must use repositories — repositories own all DB access.
```

To add a new boundary, append to the map:

```neon
parameters:
    ptgs:
        forbiddenDependencies:
            command:
                - group: dbAccess
                  reason: 'Console commands should orchestrate via services, not touch the DB directly.'
```

Matching is by FQCN string. Subtype detection is **not** supported — list every
class you want forbidden in the group, including each concrete implementation
of an interface if those are also injected directly.

### Other configurable parameters

```neon
parameters:
    ptgs:
        # Function name for NoNullInJsonSerializeRule (default: array_filter_nulls)
        nullFilterFunction: array_filter_nulls
```

## Examples

### Before

```php
// ptgs.forbiddenDependency (controller → dbAccess)
class UserController {
    public function __construct(
        private EntityManagerInterface $em,
    ) {}
}

// ptgs.noGenericId
#[Route('/users/{id}', methods: 'GET')]
public function getUserAction(string $id) {}

// ptgs.routeRequiresMethod
#[Route('/users')]
public function listUsersAction() {}

// ptgs.namedArgumentForBoolean
$service->save($entity, true);

// ptgs.noSnakeCaseJsonKey
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
