<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;

/**
 * WithUnsupportedPropertyTypeDto
 */
final class WithUnsupportedPropertyTypeDto implements HydrableObjectInterface
{
    public \Traversable $value;
}
