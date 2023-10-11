<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

use ArrayAccess;
use Countable;
use OverflowException;
use ReturnTypeWillChange;

use function count;

/**
 * @implements ArrayAccess<TKey, TValue>
 *
 * @template TKey as array-key
 * @template TValue as mixed
 */
class Collection implements ArrayAccess, Countable
{
    /**
     * @var int<-1, max>
     */
    protected const LIMIT = -1;

    /**
     * @var array<TKey, TValue>
     */
    public array $elements = [];

    /**
     * @param TKey $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->elements[$offset]);
    }

    /**
     * @param TKey $offset
     *
     * @return TValue|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->elements[$offset] ?? null;
    }

    /**
     * @param TKey $offset
     * @param TValue $value
     *
     * @return void
     *
     * @throws OverflowException
     */
    public function offsetSet($offset, $value): void
    {
        if ($this->count() === static::LIMIT) {
            throw new OverflowException();
        }

        $this->elements[$offset] = $value;
    }

    /**
     * @param TKey $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->elements[$offset]);
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->elements);
    }
}
