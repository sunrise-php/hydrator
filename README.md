# Strictly typed object hydration and value casting

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/build.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)

Languages: [🇬🇧 English](README.md) | [🇷🇺 Русский](README-ru.md)

The package hydrates object properties from arrays and JSON with casting to declared PHP types. Individual values can also be cast without hydrating an object.

## Installation

```bash
composer require sunrise/hydrator
```

The package supports PHP 7.4 and newer. Examples in this README target PHP 8+.

## Object Hydration

```php
use Sunrise\Hydrator\Annotation\Filter;
use Sunrise\Hydrator\Annotation\ItemType;
use Sunrise\Hydrator\Hydrator;

final class CreateUserRequest
{
    #[Filter('trim')]
    public string $email;

    public bool $isActive;

    #[ItemType('string', limit: 10)]
    public array $roles = [];
}

$request = (new Hydrator())->hydrate(CreateUserRequest::class, [
    'email' => ' user@example.com ',
    'isActive' => 'true',
    'roles' => ['user', 'editor'],
]);
```

The hydrator:

- creates an object without calling its constructor or fills an existing object;
- skips properties marked with `#[Ignore]`;
- ignores input keys that do not match any property;
- hydrates nested objects recursively;
- collects property and element hydration errors into one `InvalidDataException`.

An input value is required when the corresponding property is not initialized and no default value is defined for it. A default value can be defined in the property declaration, in the constructor parameter with the same name, or through `#[DefaultValue]`.

Default values are used only when the input key is missing. A passed `null` is treated as an input value and is validated against the property type.

### Hydration from JSON

```php
$request = (new Hydrator())->hydrateWithJson(
    CreateUserRequest::class,
    '{"email":"user@example.com","isActive":true}'
);
```

The root JSON value must be an object or an array.

### Value Casting

```php
use Sunrise\Hydrator\Dictionary\BuiltinType;
use Sunrise\Hydrator\Hydrator;
use Sunrise\Hydrator\Type;

$value = (new Hydrator())->castValue(
    '42',
    Type::fromName(BuiltinType::INT)
);
```

## Supported Types

| PHP type | Accepted input values |
| --- | --- |
| `mixed` | Any value |
| `bool` | `bool` or the strings `1`, `0`, `true`, `false`, `yes`, `no`, `on`, `off` |
| `int` | `int` or a string representation of an integer |
| `float` | `float`, `int`, or a string representation of a number |
| `string` | `string` or `int` |
| `array` | `array` or `stdClass` |
| `DateTimeImmutable` and subclasses | A string in the configured format; an integer or string for format `U` |
| `DateInterval` | A string accepted by the `DateInterval` constructor |
| `DateTimeZone` | A timezone identifier |
| A concrete backed enum | A value of the corresponding backing type; for `int`, a string representation of an integer is also accepted |
| An instantiable user-defined class | `array` or `stdClass` |
| A class implementing `ArrayAccess` | `array` or `stdClass` |

Also supported:

| Type | Package |
| --- | --- |
| A subclass of `MyCLabs\Enum\Enum` | `myclabs/php-enum` |
| `Ramsey\Uuid\UuidInterface` | `ramsey/uuid` |
| Subclasses of `Symfony\Component\Uid\AbstractUid` | `symfony/uid` |

`null` is accepted only for a type that allows `null`.
For booleans, numbers, dates, intervals, timezones, enums, UUIDs, and UIDs, an empty string after trimming is treated as a missing value: `null` is returned for a nullable type; otherwise an `InvalidValueException` is thrown.
Union and intersection types are not supported.

## Arrays and Collections

`#[ItemType]` defines the type of array or collection elements and optionally limits their count:

```php
use Sunrise\Hydrator\Annotation\ItemType;

final class OrderRequest
{
    #[ItemType(OrderItemRequest::class, limit: 100)]
    public array $items;
}
```

Each element will be cast to the declared type. The `allowsNull` parameter allows `null` values in elements:

```php
#[ItemType('int', allowsNull: true, limit: 100)]
public array $ids;
```

For an instantiable class implementing `ArrayAccess`, the element type can also be inferred from the type of the last constructor parameter declared with `...`:

```php
final class UserCollection extends ArrayObject
{
    public function __construct(User ...$users)
    {
        parent::__construct($users);
    }
}
```

The constructor is not called during hydration: the hydrator only reads the type of its last variadic parameter and creates the object without the constructor. An explicit `#[ItemType]` takes precedence over the constructor parameter type.

### Element Types from PHPDoc

The hydrator can read the element type of an `array` property from `@var`:

```php
final class OrderRequest
{
    /** @var list<OrderItemRequest> */
    public array $items;
}

$hydrator = new Hydrator(isDocBlockReaderEnabled: true);
```

PHPDoc reading is disabled by default. `#[ItemType]` takes precedence over `@var`.
`@var` is not used for classes implementing `ArrayAccess`. Use `#[ItemType]` or the type of the variadic constructor parameter instead.

## Attributes

| Attribute | Purpose |
| --- | --- |
| `#[Alias('external-name')]` | Defines the input key for a property |
| `#[Context([...])]` | Defines or overrides context values for a property or parameter |
| `#[DefaultValue(...)]` | Defines a value used when the input key is missing |
| `#[Filter(...)]` | Transforms the input value before type casting |
| `#[Format('...')]` | Defines the date and time format |
| `#[Ignore]` | Excludes a property from hydration |
| `#[ItemType(...)]` | Defines the element type and maximum element count |

`#[Filter]` can be used more than once. Filters are applied in sequence:

```php
use Sunrise\Hydrator\Annotation\Filter;

#[Filter('trim')]
#[Filter('strtolower')]
public string $email;
```

`#[Filter]` is available only on PHP 8.0 and newer.

## Date, Time, and Context

By default, `DateTimeImmutable` uses the `DateTimeInterface::RFC3339_EXTENDED` format.

Format and timezone can be configured for a hydrator instance:

```php
use DateTimeInterface;
use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Hydrator;

$hydrator = new Hydrator(context: [
    ContextKey::TIMESTAMP_FORMAT => DateTimeInterface::RFC3339_EXTENDED,
    ContextKey::TIMEZONE => 'Europe/Belgrade',
]);
```

Operation-specific context is passed to `hydrate()` or `castValue()`. Use `#[Context]` and `#[Format]` for a property or parameter:

```php
use Sunrise\Hydrator\Annotation\Context;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\Dictionary\ContextKey;

#[Format('Y-m-d')]
#[Context([ContextKey::TIMEZONE => 'UTC'])]
public DateTimeImmutable $date;
```

Precedence order:

1. attribute context;
2. operation context;
3. hydrator context.

## Errors

`hydrate()` collects individual value errors into `InvalidDataException`:

```php
use Sunrise\Hydrator\Exception\InvalidDataException;

try {
    $request = $hydrator->hydrate(CreateUserRequest::class, $data);
} catch (InvalidDataException $e) {
    foreach ($e->getExceptions() as $error) {
        echo $error->getPropertyPath() . ': ' . $error->getMessage() . PHP_EOL;
    }
}
```

`InvalidValueException` provides:

- `getPropertyPath()` — dot-separated value path;
- `getErrorCode()` — error code;
- `getMessage()` — resolved message;
- `getMessageTemplate()` — message template;
- `getMessagePlaceholders()` — template parameters;
- `getInvalidValue()` — original value;
- `getTranslationDomain()` — translation domain.

Method exceptions:

- `hydrate()` throws `InvalidDataException` for input value errors and `InvalidObjectException` when the object cannot be created or a property has an unsupported type;
- `hydrateWithJson()` additionally uses `InvalidDataException` for JSON decoding errors and an invalid root value;
- `castValue()` throws `InvalidValueException` for a single invalid value, `InvalidDataException` for nested object or array errors, and `InvalidObjectException` for an unsupported target type.

If `symfony/validator` is installed, `InvalidValueException::getViolation()` and `InvalidDataException::getViolations()` return violations in the Symfony Validator format.

## Custom Type Converters

A converter implements `TypeConverterInterface`. If the type is not supported, the method returns without a result. If it is supported, it yields the result or throws an exception.

```php
use Generator;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;

final class MoneyTypeConverter implements TypeConverterInterface
{
    public function castValue(
        $value,
        Type $type,
        array $path,
        array $context
    ): Generator {
        if ($type->getName() !== Money::class) {
            return;
        }

        if (!is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        yield Money::fromString($value);
    }

    public function getWeight(): int
    {
        return 100;
    }
}
```

A converter can be passed to the constructor or added later:

```php
$hydrator = new Hydrator(typeConverters: [
    new MoneyTypeConverter(),
]);

$hydrator->addTypeConverter(new MoneyTypeConverter());
```

Converters are called in descending weight order. Implement `HydratorAwareInterface` or `AnnotationReaderAwareInterface` if a converter needs access to the hydrator or the annotation reader.

## Compatibility

On PHP 8.0 and newer, attributes are read automatically. On PHP 7.4, Doctrine Annotations can be used for `Alias`, `Context`, `DefaultValue`, `Format`, `Ignore`, and `ItemType`:

```bash
composer require doctrine/annotations
```

```php
use Sunrise\Hydrator\AnnotationReader\DoctrineAnnotationReader;

$hydrator->setAnnotationReader(DoctrineAnnotationReader::default());
```

On PHP 7, only `DateTimeImmutable` itself is supported, not its subclasses. Built-in enums are supported starting with PHP 8.1.

Deprecated names `#[Subtype]` and `#[Relationship]` are kept for backward compatibility. Use `#[ItemType]` in new code.
