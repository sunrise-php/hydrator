<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Enum;

use function array_map;

final class CustomEnum extends Enum
{
    public const Int1 = 1;
    public const Int2 = 2 ;

    public const String1 = 'foo';
    public const String2 = 'bar';

    public const Another = [];

    public static function values() : array
    {
        return array_map(function ($case) {
            return $case->value();
        }, self::cases());
    }
}
