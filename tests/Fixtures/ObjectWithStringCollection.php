<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\ObjectCollection;

final class ObjectWithStringCollection extends ObjectCollection
{
    public const T = ObjectWithString::class;
}
