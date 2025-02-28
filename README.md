# Strongly typed hydrator for PHP 7.4+ with extensibility

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/build.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Total Downloads](https://poser.pugx.org/sunrise/hydrator/downloads?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![Latest Stable Version](https://poser.pugx.org/sunrise/hydrator/v/stable?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![License](https://poser.pugx.org/sunrise/hydrator/license?format=flat)](https://packagist.org/packages/sunrise/hydrator)

**php**, **dto**, **hydrator**, **mapper**, **populator**, **data-mapper**

---

## Installation

```bash
composer require sunrise/hydrator
```

## How to use

Let's consider a typical DTO set:

```php
enum Status: int
{
    case Disabled = 0;
    case Enabled = 1;
}
```

```php
final readonly class CategoryDto
{
    public function __construct(
        public string $name,
    ) {
    }
}
```

```php
final readonly class TagDto
{
    public function __construct(
        public string $name,
    ) {
    }
}
```

```php
final class TagDtoCollection extends ArrayObject
{
    public function __construct(TagDto ...$tags)
    {
        parent::__construct($tags);
    }
}
```

```php
final readonly class PublicationDto
{
    public function __construct(
        public string $name,
        public CategoryDto $category,
        public TagDtoCollection $tags,
        public Status $status = Status::Disabled,
        #[\Sunrise\Hydrator\Annotation\Format(DateTimeInterface::RFC3339)]
        public DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {
    }
}
```

Now, let's populate them all from an array:

```php
$data = [
    'name' => 'Some product',
    'category' => [
        'name' => 'Some category',
    ],
    'tags' => [
        [ 'name' => 'foo' ],
        [ 'name' => 'bar' ],
    ],
    'status' => 0,
];

$product = (new \Sunrise\Hydrator\Hydrator)->hydrate(PublicationDto::class, $data);
```

Or, you can populate them using JSON:

```php
$json = <<<JSON
{
    "name": "Some product",
    "category": {
        "name": "Some category"
    },
    "tags": [
        { "name": "foo" },
        { "name": "bar" }
    ],
    "status": 0
}
JSON;

$product = (new \Sunrise\Hydrator\Hydrator)->hydrateWithJson(PublicationDto::class, $json);
```

## Allowed property types

### Required

```php
public readonly string $value;
```

If a property has no a default value, then the property is required.

### Optional

```php
public string $value = 'foo';
```

If a property has a default value, then the property is optional.

There are situations when it is necessary to assign a value to a readonly property. This cannot be done outside the constructor; however, you can use a special annotation, as shown in the example below:

```php
#[\Sunrise\Hydrator\Annotation\DefaultValue('foo')]
public readonly string $value;
```

### Null

```php
public readonly ?string $value;
```

If a property is nullable, then the property can accept null.

Also, please note that empty strings or strings consisting only of whitespace will be handled as null for certain properties.

### Boolean

```php
public readonly bool $value;
```

A boolean value in a dataset can be represented as:

| true   | false   | type   |
|--------|---------|--------|
| true   | false   | bool   |
| "1"    | "0"     | string |
| "true" | "false" | string |
| "yes"  | "no"    | string |
| "on"   | "off"   | string |

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

### Integer

```php
public readonly int $value;
```

An integer value in a dataset can also be represented as a string, e.g., "42".

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

### Number

```php
public readonly float $value;
```

A numerical value in a dataset can be represented not only as an integer or a floating-point number but also as a string containing a number. However, regardless of how such a value is represented in the dataset, it will always be stored in this property as a floating-point number.

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

### String

```php
public readonly string $value;
```

This property has no any additional behavior and only accepts strings.

### Array

```php
public readonly array $value;
```

By default, this property accepts an array with any data. However, it can also be used to store relationships by using a special annotation, as shown in the example below:

```php
#[\Sunrise\Hydrator\Annotation\Subtype(SomeDto::class)]
public readonly array $value;
```

> In other words, the **Subtype** annotation can contain the same types as the types of class properties...

Having an unlimited number of relationships in an array is a potentially bad idea as it can lead to memory leaks. To avoid this, it is recommended to limit such an array, as shown in the example below:

```php
#[\Sunrise\Hydrator\Annotation\Subtype(SomeDto::class, limit: 100)]
public readonly array $value;
```

In addition to arrays, you can also use **collections**, in other words, classes implementing the [ArrayAccess](http://php.net/ArrayAccess) interface, for example:

```php
final class TagDto
{
}
```

```php
final class TagDtoCollection implements \ArrayAccess
{
}
```

```php
final class CreateProductDto
{
    public function __construct(
        #[\Sunrise\Hydrator\Annotation\Subtype(TagDto::class, limit: 10)]
        public readonly TagDtoCollection $tags,
    ) {
    }
}
```

Note that for collections, instead of the **Subtype** annotation, you can use typing through its constructor. It is important that there is only one variadic parameter in it. Please refer to the example below:

> Please note that in this case, you take on the responsibility of limiting the collection. To ensure that the hydrator understands when the collection is full, the [offsetSet](https://www.php.net/arrayaccess.offsetset) method should throw an [OverflowException](https://www.php.net/overflowexception).

```php
final class TagDtoCollection implements \ArrayAccess
{
    public function __construct(public TagDto ...$tags)
    {
    }
}
```

```php
final class CreateProductDto
{
    public function __construct(
        public readonly TagDtoCollection $tags,
    ) {
    }
}
```

In general, remember that regardless of whether arrays or collections are used, their elements can be typed. For example, if you need an array that should consist only of dates, your code should look something like this:

```php
#[\Sunrise\Hydrator\Annotation\Subtype(\DateTimeImmutable::class, limit: 100)]
#[\Sunrise\Hydrator\Annotation\Format('Y-m-d H:i:s')]
public readonly array $value;
```

Sometimes, there is a need to map a typed list to a collection. It's a relatively simple task; just use the example below:

```php
use Sunrise\Hydrator\Type;

$data = [...];

$collection = $hydrator->castValue($data, Type::fromName(SomeDtoCollection::class));
```

This property has no any additional behavior and only accepts arrays.

### Timestamp

Only the [DateTimeImmutable](https://www.php.net/DateTimeImmutable) type is supported.

```php
#[\Sunrise\Hydrator\Annotation\Format('Y-m-d H:i:s')]
public readonly DateTimeImmutable $value;
```

This property accepts a date as a string in the specified format, but it can also accept a Unix timestamp as an integer or a string. To specify the Unix timestamp format, it should be indicated as follows:

```php
#[\Sunrise\Hydrator\Annotation\Format('U')]
public readonly DateTimeImmutable $value;
```

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

#### Default timestamp format

```php
use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Hydrator;

$hydrator = new Hydrator([
    ContextKey::TIMESTAMP_FORMAT => 'Y-m-d H:i:s',
]);
```

### Timezone

Only the [DateTimeZone](https://www.php.net/DateTimeZone) type is supported.

```php
public readonly DateTimeZone $value;
```

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

#### Default timezone

```php
use Sunrise\Hydrator\Dictionary\ContextKey;
use Sunrise\Hydrator\Hydrator;

$hydrator = new Hydrator([
    ContextKey::TIMEZONE => 'Europe/Kyiv',
]);
```

### UUID

#### Using the [ramsey/uuid](https://github.com/ramsey/uuid) package

```bash
composer require ramsey/uuid
```

```php
public readonly \Ramsey\Uuid\UuidInterface $value;
```

#### Using the [symfony/uid](https://github.com/symfony/uid) package

```bash
composer require symfony/uid
```

```php
public readonly \Symfony\Component\Uid\UuidV4 $value;
```

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

### Enumeration

#### PHP 8.1 [built-in](https://www.php.net/enum) enumerations

```php
public readonly SomeEnum $value;
```

This property should be typed only with typed enumerations. Therefore, for integer enumerations, a value in a dataset can be either an integer or an integer represented as a string. For string enumerations, a value in a dataset can only be a string.

#### [MyCLabs](https://github.com/myclabs/php-enum) enumerations

_The popular alternative for PHP less than 8.1..._

```bash
composer require myclabs/php-enum
```

```php
public readonly SomeEnum $value;
```

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).

### Relationship

```php
public readonly SomeDto $value;
```

A value in a dataset can only be an array. However, please note that if you need a one-to-many relationship, you should refer to the [array](#array) section for further information.

## Support for custom types

If you need support for a custom type, it is a relatively simple task. Let's write such support for PSR-7 URI from the [sunrise/http-message](https://github.com/sunrise-php/http-message) package:

```bash
composer require sunrise/http-message
```

```php
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Type;
use Sunrise\Hydrator\TypeConverterInterface;
use Psr\Message\UriInterface;
use Sunrise\Http\Message\Uri;

final class UriTypeConverter implements TypeConverterInterface
{
    public function castValue($value, Type $type, array $path): Generator
    {
        if ($type->getName() <> UriInterface::class) {
            return;
        }

        if (!\is_string($value)) {
            throw InvalidValueException::mustBeString($path, $value);
        }

        try {
            yield new Uri($value);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidValueException(
                'This value is not a valid URI.',
                'c66741c6-e3c0-4522-a8e3-97528d7712a3',
                $path,
            );
        }
    }

    public function getWeight(): int
    {
        return 0;
    }
}
```

Now, let's inform the hydrator about the new type:

```php
$hydrator->addTypeConverter(new UriTypeConverter());
```

## Ignored property

If you need a property to be ignored and not populated during the object hydration process, use a special annotation like the example below:

```php
#[\Sunrise\Hydrator\Annotation\Ignore]
public string $value;
```

## Property alias

If you need to handle an unnormalized key in a dataset or have other reasons to associate such a key with a property that has a different name, you can use a special annotation for this purpose:

```php
#[\Sunrise\Hydrator\Annotation\Alias('error-codes')]
public array $errorCodes = [];
```

## Error handling

```php
try {
    $hydrator->hydrate(...);
} catch (\Sunrise\Hydrator\Exception\InvalidDataException $e) {
    // It's runtime error
} catch (\Sunrise\Hydrator\Exception\InvalidObjectException $e) {
    // It's logic error
}
```

The `InvalidDataException` exception contains errors related to an input dataset and is designed to display errors directly on the client side.

If you are using the `symfony/validator` package, you may find it useful to present the errors as a `\Symfony\Component\Validator\ConstraintViolationListInterface`. To achieve this, you can call the following method on this exception:

```php
try {
    $hydrator->hydrate(...);
} catch (\Sunrise\Hydrator\Exception\InvalidDataException $e) {
    $violations = $e->getViolations();
}
```

Or you can retrieve the list of errors in the standard way, as shown in the example below:

```php
try {
    $hydrator->hydrate(...);
} catch (\Sunrise\Hydrator\Exception\InvalidDataException $e) {
    $errors = $e->getExceptions();
    foreach ($errors as $error) {
        echo $error->getMessage(), PHP_EOL;
        echo $error->getPropertyPath(), PHP_EOL;
        echo $error->getErrorCode(), PHP_EOL;
    }
}
```

## Localization

```php
$localizedMessages = [
    // using the error code dictionary...
    \Sunrise\Hydrator\Dictionary\ErrorCode::INVALID_TIMESTAMP => 'Это значение не является валидной временной меткой; ожидаемый формат: {{ expected_format }}.',

    // or using the error message dictionary...
    \Sunrise\Hydrator\Dictionary\ErrorMessage::INVALID_TIMESTAMP => 'Это значение не является валидной временной меткой; ожидаемый формат: {{ expected_format }}.',
];

try {
    $hydrator->hydrate(...);
} catch (\Sunrise\Hydrator\Exception\InvalidDataException $e) {
    foreach ($e->getExceptions() as $error) {
        // original message...
        $message = $error->getMessage();

        // using the error code dictionary...
        if (isset($localizedMessages[$error->getErrorCode()])) {
            // localized message
            $message = \strtr($localizedMessages[$error->getErrorCode()], $error->getMessagePlaceholders()), PHP_EOL;
        }

        // or using the error message dictionary...
        if (isset($localizedMessages[$error->getMessageTemplate()])) {
            // localized message
            $message = \strtr($localizedMessages[$error->getMessageTemplate()], $error->getMessagePlaceholders()), PHP_EOL;
        }
    }
}
```

## Doctrine annotations

To use annotations, you need to install the `doctrine/annotations` package:

```bash
composer require doctrine/annotations
```

To use annotations in PHP 7 or explicitly in PHP 8, you can use the following method of the hydrator:

```php
$hydrator->useDefaultAnnotationReader();
```

If you need to provide your instance of an annotation reader, you can use the following method:

```php
$hydrator->setAnnotationReader(...);
```

---

## Test run

```bash
composer test
```
