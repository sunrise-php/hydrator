<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;

/**
 * TestJsonDto
 */
final class TestJsonDto implements HydrableObjectInterface
{
    public TestJsonableObject $json;
}
