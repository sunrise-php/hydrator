# Strongly typed hydrator for PHP 7.4+ with support for PHP 8.1 enums

**hydrator**, **mapper**, **dto**, **enum**

[![Build Status](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/build.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Total Downloads](https://poser.pugx.org/sunrise/hydrator/downloads?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![Latest Stable Version](https://poser.pugx.org/sunrise/hydrator/v/stable?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![License](https://poser.pugx.org/sunrise/hydrator/license?format=flat)](https://packagist.org/packages/sunrise/hydrator)

---

## Installation

```bash
composer require sunrise/hydrator
```

## How to use?

```php
use Sunrise\Hydrator\Hydrator;

$hydrator = new Hydrator();

// disable support for alias mechanism
// available since version v2.5.0
$hydrator->aliasSupport(false);

// enable support for annotations
// for php8 it is recommended to use attributes
$hydrator->useAnnotations();

// create and hydrate an object with an array
$data = [/* the class props here */];
$object = $hydrator->hydrate(SomeDto::class, $data);

// hydrate an object with an array
$data = [/* the class props here */];
$hydrator->hydrate($object, $data);

// creates and hydrate an object with JSON
$json = '';
$object = $hydrator->hydrateWithJson(SomeDto::class, $json);

// hydrate an object with JSON
$json = '';
$hydrator->hydrateWithJson($object, $json);

// pass JSON decoding flags
$options = JSON_OBJECT_AS_ARRAY|JSON_BIGINT_AS_STRING;
$hydrator->hydrateWithJson($object, $json, $options);
```

## Allowed property types

### Required

If a property has no a default value, then the property is required.

```php
public readonly string $value;
```

### Optional

If a property has a default value, then the property is optional.

```php
public readonly string $value = 'foo';
```

### Null

If a property is nullable, then the property can accept null.

```php
public readonly ?string $value;
```

If the property should be optional, then it must has a default value.

```php
public readonly ?string $value = null;
```

### Boolean

Accepts the following values: true, false, 1, 0, "1", "0", "yes", "no", "on" and "no".

```php
public readonly bool $value;
```

```php
['value' => true];
['value' => 'yes'];
```

## Integer

Accepts only integers (also as a string).

```php
public readonly int $value;
```

```php
['value' => 42];
['value' => '42'];
```

## Number<int|float>

Accepts only numbers (also as a string).

```php
public readonly float $value;
```

```php
['value' => 42.0];
['value' => '42.0'];
```

## String

Accepts only strings.

```php
public readonly string $value;
```

```php
['value' => 'foo'];
```

## Array<array-key, mixed>

Accepts only arrays.

```php
public readonly array $value;
```

```php
['value' => []];
```

## Object

Accepts only objects.

```php
public readonly object $value;
```

```php
['value' => new stdClass];
```

## DateTime/DateTimeImmutable

Integers (also as a string) will be handled as a timestamp, otherwise accepts only valid date-time strings.

```php
public readonly DateTimeImmutable $value;
```

```php
// 2010-01-01
['value' => 1262304000];
// 2010-01-01
['value' => '1262304000'];
// normal date
['value' => '2010-01-01'];
```

## DateInterval

Accepts only valid date-interval strings based on ISO 8601.

```php
public readonly DateInterval $value;
```

```php
['value' => 'P1Y']
```

## Enum<BackedEnum>

Accepts only values that exist in an enum.

```php
enum SomeEnum: int {
    case foo = 0;
    case bar = 1;
}
```

```php
public readonly SomeEnum $value;
```

```php
['value' => 0]
['value' => '1']
```

## Enum for PHP < 8.1

Accepts only values that exist in an enum.

```php
use Sunrise\Hydrator\Enum;

final class SomeEnum extends Enum {
    public const foo = 0;
    public const bar = 1;
}
```

```php
public SomeEnum $value;
```

```php
['value' => 0]
['value' => '1']
```

#### Useful to know

```php
// returns all cases of the enum
SomeEnum::cases();

// initializes the enum by the case's name
$case = SomeEnum::foo();

// initializes the enum by the case's value
$case = SomeEnum::tryFrom(0);

// gets the name of the enum's case
$case->name()

// gets the value of the enum's case
$case->value()
```

## Association

Accepts a valid structure for an association

```php
final class SomeDto {
    public readonly string $value;
}
```

```php
public readonly SomeDto $value;
```

```php
[
    'value' => [
        'value' => 'foo',
    ],
]
```

## AssociationCollection<ObjectCollectionInterface<T>>

Accepts a list of an association's valid structures.

```php
use Sunrise\Hydrator\ObjectCollection;

final class SomeCollection extends ObjectCollection {
    public const T = SomeDto::class;
}

final class SomeDto {
    public readonly string $value;
}
```

```php
public readonly SomeCollection $value;
```

```php
[
    'value' => [
        [
            'value' => 'foo',
        ],
        [
            'value' => 'bar',
        ],
    ],
],
```

## Property alias

If you need to get a non-normalized key, use aliases.

For example, the Google Recaptcha API returns the following response:

```json
{
    "success": false,
    "error-codes": []
}
```

To correctly map the response, use the following model:

```php
use Sunrise\Hydrator\Annotation\Alias;

final class RecaptchaVerificationResult {
    public bool $success;

    #[Alias('error-codes')]
    public array $errorCodes = [];
}
```

Please note, if you are using PHP 7 then you need to enable annotation support and use the following model:

```php
$hydrator->useAnnotations();
```

```php
use Sunrise\Hydrator\Annotation\Alias;

final class RecaptchaVerificationResult {
    public bool $success;

    /**
     * @Alias("error-codes")
     */
    public array $errorCodes = [];
}
```

## Examples

```php
final class Product {
    public readonly string $name;
    public readonly Category $category;
    public readonly TagCollection $tags;
    public readonly Status $status;
}

final class Category {
    public readonly string $name;
}

final class TagCollection extends \Sunrise\Hydrator\ObjectCollection {
    // the collection will only accept this type
    public const T = Tag::class;
}

final class Tag {
    public readonly string $name;
}

enum Status: int {
    case ENABLED = 1;
    case DISABLED = 0;
}
```

```php
$product = $hydrator->hydrate(Product::class, [
    'name' => 'Stool',
    'category' => [
        'name' => 'Furniture',
    ],
    'tags' => [
        [
            'name' => 'Wood',
        ],
        [
            'name' => 'Lacquered',
        ],
    ],
    'status' => 0,
]);
```

---

## Test run

```bash
composer test
```
