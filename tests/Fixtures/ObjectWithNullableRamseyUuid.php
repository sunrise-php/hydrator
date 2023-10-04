<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Ramsey\Uuid\UuidInterface;

final class ObjectWithNullableRamseyUuid
{
    public ?UuidInterface $value;
}
