<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use ArrayAccess;
use ReturnTypeWillChange;

final class Collection implements ArrayAccess
{
    public array $elements = [];

    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }

    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->elements[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }
}