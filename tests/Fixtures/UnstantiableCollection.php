<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use ArrayAccess;
use OverflowException;
use ReturnTypeWillChange;

final class UnstantiableCollection implements ArrayAccess
{
    private function __construct()
    {
    }

    public function offsetExists($offset): bool
    {
        return false;
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new OverflowException();
    }

    public function offsetUnset($offset): void
    {
    }
}
