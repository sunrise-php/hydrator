<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Relationship;

final class ObjectWithOptionalRelationships
{
    /**
     * @Relationship(ObjectWithString::class)
     *
     * @var list<ObjectWithString>
     */
    #[Relationship(ObjectWithString::class)]
    public array $value = [];
}
