<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;

/**
 * WithUntypedPropertyDto
 */
final class WithUntypedPropertyDto implements HydrableObjectInterface
{
    public $value;
}
