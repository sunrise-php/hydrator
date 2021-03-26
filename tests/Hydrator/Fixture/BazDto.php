<?php declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

/**
 * Import classes
 */
use Sunrise\Hydrator\HydrableObjectInterface;
use ArrayIterator;
use DateTimeImmutable;

/**
 * BazDto
 */
final class BazDto implements HydrableObjectInterface
{
    public string $nonNullable = 'default value';
    public ?string $scalar = null;
    public ?ArrayIterator $array = null;
    public ?DateTimeImmutable $dateTime = null;
    public ?BarDto $oneToOne = null;
    public ?BarDtoCollection $oneToMany = null;
}
