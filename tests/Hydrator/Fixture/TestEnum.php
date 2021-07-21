<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\EnumerableObject;

/**
 * TestEnum
 */
final class TestEnum extends EnumerableObject
{
    public const A = 'A:value';
    public const B = 'B:value';
    public const C = 'C:value';
}
