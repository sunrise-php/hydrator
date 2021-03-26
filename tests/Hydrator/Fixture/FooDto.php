<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;
use ArrayIterator;
use DateTimeImmutable;

/**
 * FooDto
 */
final class FooDto implements HydrableObjectInterface
{
    public static string $static = 'default value';
    public string $valuable = 'default value';
    public ?string $nullable;
    public bool $bool;
    public int $int;
    public float $float;
    public string $string;
    public ArrayIterator $array;
    public DateTimeImmutable $dateTime;
    public BarDto $barDto;
    public BarDtoCollection $barDtoCollection;

    /**
     * @Alias("alias")
     */
    public string $hidden;
}
