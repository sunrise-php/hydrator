<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\ObjectCollection;

final class BarCollection extends ObjectCollection
{
    public const T = Bar::class;
}
