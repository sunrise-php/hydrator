<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeZone;

final class ObjectWithOptionalTimezone
{
    public ?DateTimeZone $value = null;
}
