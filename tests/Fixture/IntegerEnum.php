<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

enum IntegerEnum: int
{
    case FOO = 1697021457;
    case BAR = 1697021458;
    case BAZ = 1697021459;

    /**
     * @return list<int>
     */
    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[] = $case->value;
        }

        return $values;
    }
}
