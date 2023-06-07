<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Relationship;

final class ObjectWithRelationshipsWithLimit
{
    /**
     * @Relationship(ObjectWithString::class, limit=1)
     */
    #[Relationship(ObjectWithString::class, limit: 1)]
    public array $value;
}
