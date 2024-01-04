<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * @extends Collection<array-key, mixed>
 */
final class UnstantiableCollection extends Collection
{
    private function __construct()
    {
    }
}
