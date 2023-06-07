<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Relationship;

final class ObjectWithRelationships
{
    /**
     * @Relationship(ObjectWithRelationship::class)
     *
     * @var list<ObjectWithRelationship>
     */
    #[Relationship(ObjectWithRelationship::class)]
    public array $value;
}
