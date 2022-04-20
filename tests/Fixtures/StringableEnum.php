<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use function array_map;

enum StringableEnum: string
{
    case foo = 'c1200a7e-136e-4a11-9bc3-cc937046e90f';
    case bar = 'a2b29b37-1c5a-4b36-9981-097ddd25c740';
    case baz = 'c1ea3762-9827-4c0c-808b-53be3febae6d';

    public static function values() : array
    {
        return array_map(fn($case) => $case->value, static::cases());
    }
}
