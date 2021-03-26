<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectCollection;

/**
 * BarDtoCollection
 */
final class BarDtoCollection extends HydrableObjectCollection
{
    public const T = BarDto::class;
}
