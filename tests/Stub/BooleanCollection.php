<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

/**
 * @extends Collection<array-key, bool>
 */
final class BooleanCollection extends Collection
{
    public function __construct(bool ...$elements)
    {
    }
}
