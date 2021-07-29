<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\JsonableObjectInterface;

/**
 * TestJsonableObject
 */
final class TestJsonableObject implements JsonableObjectInterface
{
    public string $foo;
    public string $bar;
}
