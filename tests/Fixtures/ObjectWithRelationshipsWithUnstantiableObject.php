<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Relationship;

final class ObjectWithRelationshipsWithUnstantiableObject
{
    /**
     * @Relationship(UnstantiableObject::class)
     */
    #[Relationship(UnstantiableObject::class)]
    public array $value;
}
