<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub\Store;

final class Category
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
