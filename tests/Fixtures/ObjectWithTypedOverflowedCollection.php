<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Subtype;

final class ObjectWithTypedOverflowedCollection
{
    #[Subtype('string')]
    public OverflowedCollection $value;
}
