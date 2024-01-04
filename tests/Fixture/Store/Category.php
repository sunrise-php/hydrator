<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture\Store;

final class Category
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
