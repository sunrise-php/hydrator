<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * @extends Collection<array-key, mixed>
 */
final class LimitedCollection extends Collection
{
    protected const LIMIT = 1;
}
