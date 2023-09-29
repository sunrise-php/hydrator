<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Symfony\Component\Uid\UuidV4;

final class ObjectWithOptionalUid
{
    public ?UuidV4 $value = null;
}
