# Strongly typed hydrator for PHP 7.4+ (incl. PHP 8)

> Great tool for your DTOs...

[![Build Status](https://circleci.com/gh/sunrise-php/hydrator.svg?style=shield)](https://circleci.com/gh/sunrise-php/hydrator)
[![Code Coverage](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/sunrise-php/hydrator/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/sunrise-php/hydrator/?branch=main)
[![Total Downloads](https://poser.pugx.org/sunrise/hydrator/downloads?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![Latest Stable Version](https://poser.pugx.org/sunrise/hydrator/v/stable?format=flat)](https://packagist.org/packages/sunrise/hydrator)
[![License](https://poser.pugx.org/sunrise/hydrator/license?format=flat)](https://packagist.org/packages/sunrise/hydrator)

---

## Installation

```bash
composer require 'sunrise/hydrator:^2.0'
```

## How to use?

```php
// hydrate an object with array:
$object = (new \Sunrise\Hydrator\Hydrator)->hydrate(Foo::class, $data);

// or you can hydrate the object with JSON:
$object = (new \Sunrise\Hydrator\Hydrator)->hydrate(Foo::class, $json);

// output the result:
var_dump($object);
```

```php
final class Foo
{
    // statical properties will ignored
    public static string $statical = '50f4e382-2858-4991-b045-a121004cec80';

    private ?string $nullable = 'ed0110a9-01ac-4f75-a205-223c98d2d2b5';
    private string $valuable = '5bf11aa0-08b3-4429-a6d7-4ebf6d70919c';
    private string $required;

    private bool $boolean; // also accepts strings (1, on, yes, etc.)
    private int $integer; // also accepts string numbers
    private float $number; // also accepts string numbers
    private string $string;
    private array $array;
    private object $object;

    private \DateTime $dateTime; // accepts timestamps and string date-time
    private \DateTimeImmutable $dateTimeImmutable; // accepts timestamps and string date-time

    private Bar $bar; // see bellow...
    private BarCollection $barCollection; // see bellow...

    /**
     * @Alias("non-normalized")
     */
    #[Alias('non-normalized')]
    private string $normalized;

    // getters...
}
```

```php
final class Bar
{
    public string $value;
}
```

```php
use Sunrise\Hydrator\ObjectCollection;

final class BarCollection extends ObjectCollection
{
    // the collection will contain only the specified objects
    public const T = Bar::class;
}
```

```php
$data = [
    'statical' => '813ea72c-6763-4596-a4d6-b478efed61bb',
    'nullable' => null,
    'required' => '9f5c273e-1dca-4c2d-ac81-7d6b03b169f4',
    'boolean' => true,
    'integer' => 42,
    'number' => 123.45,
    'string' => 'db7614d4-0a81-437b-b2cf-c536ad229c97',
    'array' => ['foo' => 'bar'],
    'object' => (object) ['foo' => 'bar'],
    'dateTime' => '2038-01-19 03:14:08',
    'dateTimeImmutable' => '2038-01-19 03:14:08',
    'bar' => [
        'value' => '9898fb3b-ffb0-406c-bda6-b516423abde7',
    ],
    'barCollection' => [
        [
            'value' => 'd85c17b6-6e2c-4e2d-9eba-e1dd59b75fe3',
        ],
        [
            'value' => '5a8019aa-1c15-4c7c-8beb-1783c3d8996b',
        ],
    ],
    'non-normalized' => 'f76c4656-431a-4337-9ba9-5440611b37f1',
];
```

---

## Test run

```bash
composer test
```
