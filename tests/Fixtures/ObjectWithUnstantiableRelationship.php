<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

final class ObjectWithUnstantiableRelationship
{
    public UnstantiableObject $value;
}
