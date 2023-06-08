<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures\Store;

use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\Annotation\Relationship;

final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly Category $category,
        #[Relationship(Tag::class)]
        public readonly array $tags,
        public readonly Status $status,
        #[Format('Y-m-d H:i:s')]
        public readonly DateTimeImmutable $createdAt = new DateTimeImmutable('2020-01-01 12:00:00'),
    ) {
    }
}
