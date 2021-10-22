<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Alias;

final class FooDto
{
    public static string $static = 'default value';
    public string $valuable = 'default value';
    public ?string $nullable;
    public bool $bool;
    public int $int;
    public float $float;
    public string $string;
    public DateTimeImmutable $dateTime;
    public BarDto $barDto;
    public BarDtoCollection $barDtoCollection;
    public array $simpleArray;

    /**
     * @Alias("alias")
     */
    #[Alias('alias')]
    public string $hidden;
}
