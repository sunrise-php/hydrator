<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Stub;

final class UnstantiableCollection extends Collection
{
    private function __construct()
    {
    }
}
