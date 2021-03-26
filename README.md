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
composer require 'sunrise/hydrator:^1.0'
```

## How to use?

```php
$payload = [
    'nullable' => null,
    'bool' => true,
    'int' => 1,
    'float' => 1.1,
    'string' => 'foo',
    'array' => [],
    'dateTime' => '2005-08-15T15:52:01.000+00:00',
    'barDto' => [
        'value' => 'foo',
    ],
    'barDtoCollection' => [
        [
            'value' => 'foo',
        ],
        [
            'value' => 'bar',
        ],
    ],
];
```

```php
use Sunrise\Hydrator\HydrableObjectInterface;
use ArrayIterator;
use DateTimeImmutable;

final class FooDto implements HydrableObjectInterface
{
    public string $optional = 'default value';
    public ?string $nullable;
    public bool $bool;
    public int $int;
    public float $float;
    public string $string;
    public ArrayIterator $array;
    public DateTimeImmutable $dateTime;
    public BarDto $barDto;
    public BarDtoCollection $barDtoCollection;
}
```

```php
use Sunrise\Hydrator\HydrableObjectInterface;

final class BarDto implements HydrableObjectInterface
{
    public string $value;
}
```

```php
use Sunrise\Hydrator\HydrableObjectCollection;

final class BarDtoCollection extends HydrableObjectCollection
{
    public const T = BarDto::class;
}
```

```php
use Sunrise\Hydrator\Hydrator;

$dto = new FooDto();

(new Hydrator)->hydrate($dto, $payload);

var_dump($dto);
```

---

## Test run

```bash
composer test
```
