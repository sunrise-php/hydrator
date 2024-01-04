<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

enum StringEnum: string
{
    case FOO = 'cc77dcf7-9266-43c1-b434-ff37344ee466';
    case BAR = 'ee7e1a9b-c07d-4c9a-b5f6-aed0a917a94c';
    case BAZ = 'aee53f73-e112-44a1-a3d5-63ea2b272383';

    /**
     * @return list<string>
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
