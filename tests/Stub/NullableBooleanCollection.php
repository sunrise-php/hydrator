<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

/**
 * @extends Collection<array-key, bool|null>
 */
final class NullableBooleanCollection extends Collection
{
    public function __construct(?bool ...$elements)
    {
    }
}
