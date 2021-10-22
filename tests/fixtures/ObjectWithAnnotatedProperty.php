<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

final class ObjectWithAnnotatedProperty
{

    /** @Alias("non-normalized-value") */
    public string $value;
}
