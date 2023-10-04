<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

final class TypedCollection extends Collection
{
    public function __construct(string ...$elements)
    {
    }
}
