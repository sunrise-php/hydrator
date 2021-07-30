<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

use Sunrise\Hydrator\HydrableObjectInterface;
use Sunrise\Hydrator\Json;

final class TestJsonTypeDto implements HydrableObjectInterface
{
    public Json $json;
}
