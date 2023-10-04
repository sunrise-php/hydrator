<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

use OverflowException;

final class OverflowedCollection extends Collection
{
    public function offsetSet($offset, $value): void
    {
        throw new OverflowException();
    }
}
