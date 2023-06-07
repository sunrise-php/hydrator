<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

enum StringEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
    case BAZ = 'baz';
}
