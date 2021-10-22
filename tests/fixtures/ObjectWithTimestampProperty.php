<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeImmutable;

final class ObjectWithTimestampProperty
{
    public DateTimeImmutable $value;
}
