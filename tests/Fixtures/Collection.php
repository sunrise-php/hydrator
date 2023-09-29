<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use ArrayAccess;
use OverflowException;

final class Collection implements ArrayAccess
{
    public array $elements = [];

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->elements[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        if (!empty($this->elements)) {
            throw new OverflowException();
        }

        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }
}
