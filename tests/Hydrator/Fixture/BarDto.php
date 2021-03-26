<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;

/**
 * BarDto
 */
final class BarDto implements HydrableObjectInterface
{
    public string $value;
}
