<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

/**
 * @extends Collection<array-key, bool>
 */
final class LimitedBooleanCollection extends Collection
{
    protected const LIMIT = 1;

    public function __construct(bool ...$elements)
    {
    }
}
