<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;

/**
 * TestEnumDto
 */
final class TestEnumDto implements HydrableObjectInterface
{
    public TestEnum $foo;
}
