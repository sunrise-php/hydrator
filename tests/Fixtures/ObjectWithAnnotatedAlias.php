<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Alias;

final class ObjectWithAnnotatedAlias
{
    /**
     * @Alias("non-normalized-value")
     */
    public string $value;
}
