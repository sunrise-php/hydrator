<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Symfony\Component\Uid\UuidV4;

final class ObjectWithUid
{
    public UuidV4 $value;
}
