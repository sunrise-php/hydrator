# Strongly typed hydrator for PHP 7.4+ with support for PHP 8.1 enums

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/build.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Total Downloads](https://poser.pugx.org/sunrise/hydrator/downloads?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![Latest Stable Version](https://poser.pugx.org/sunrise/hydrator/v/stable?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![License](https://poser.pugx.org/sunrise/hydrator/license?format=flat)](https://packagist.org/packages/sunrise/hydrator)

**hydrator**, **mapper**, **dto**, **data-mapper**, **model-mapper**

---

## Installation

```bash
composer require sunrise/hydrator
```

## How to use?

Let's consider a typical DTO set:

```php
enum Status: int {
    case ENABLED = 1;
    case DISABLED = 0;
}

final class Category {
    public function __construct(
        public readonly string $name,
    ) {
    }
}

final class Tag {
    public function __construct(
        public readonly string $name,
    ) {
    }
}

final class Product {
    public function __construct(
        public readonly string $name,
        public readonly Category $category,
        #[\Sunrise\Hydrator\Annotation\Relationship(Tag::class, limit: 100)]
        public readonly array $tags,
        public readonly Status $status = Status::DISABLED,
        #[\Sunrise\Hydrator\Annotation\Format('Y-m-d H:i:s')]
        public readonly DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {
    }
}
```

Now, let's populate them all from an array:

```php
$product = (new \Sunrise\Hydrator\Hydrator)->hydrate(Product::class, [
    'name' => 'Some product',
    'category' => [
        'name' => 'Some category',
    ],
    'tags' => [
        [
            'name' => 'foo',
        ],
        [
            'name' => 'bar',
        ],
    ],
    'status' => 0,
]);
```

Or, you can populate them using JSON:

```php
$product = (new \Sunrise\Hydrator\Hydrator)->hydrateWithJson(Product::class, <<<JSON
{
    "name": "Some product",
    "category": {
        "name": "Some category"
    },
    "tags": [
        {
            "name": "foo"
        },
        {
            "name": "bar"
        }
    ],
    "status": 0
}
JSON);
```

## Allowed property types

### Required

```php
public readonly string $value;
```

If a property has no a default value, then the property is required.

### Optional

```php
public readonly string $value = 'foo';
```

If a property has a default value, then the property is optional.

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
#[\Sunrise\Hydrator\Annotation\Relationship(SomeDto::class)]
public readonly array $value;
```

Having an unlimited number of relationships in an array is a potentially bad idea as it can lead to memory leaks. To avoid this, it is recommended to limit such an array, as shown in the example below:

```php
#[\Sunrise\Hydrator\Annotation\Relationship(SomeDto::class, limit: 100)]
public readonly array $value;
```

This property has no any additional behavior and only accepts arrays.

### DateTimeImmutable

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

### Enumeration

```php
public readonly SomeEnum $value;
```

This property should be typed only with typed enumerations. Therefore, for integer enumerations, a value in a dataset can be either an integer or an integer represented as a string. For string enumerations, a value in a dataset can only be a string.

Also, please note that if a value in a dataset for this property is represented as an empty string or a string consisting only of whitespace, then the value will be handled as [null](#null).


### Relationship

```php
public readonly SomeDto $value;
```

A value in a dataset can only be an array. However, please note that if you need a one-to-many relationship, you should refer to the [array](#array) section for further information.

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

## PHP 7 annotations

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
