<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub\Store;

enum Status: int
{
    case AVAILABLE = 0;
    case PENDING = 1;
    case SOLD = 2;
}
