<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

final class UninitializableObject
{
    public function __construct($value)
    {
    }
}
