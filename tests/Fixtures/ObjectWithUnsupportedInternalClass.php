<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use SplFileInfo;

final class ObjectWithUnsupportedInternalClass
{
    public SplFileInfo $value;
}
