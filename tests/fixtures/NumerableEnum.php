<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use function array_map;

enum NumerableEnum: int
{
    case foo = 1;
    case bar = 2;
    case baz = 3;

    public static function values() : array
    {
        return array_map(fn($case) => $case->value, static::cases());
    }
}
