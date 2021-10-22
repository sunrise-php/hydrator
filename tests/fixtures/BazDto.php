<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeImmutable;

final class BazDto
{
    public string $nonNullable = 'default value';
    public ?string $scalar = null;
    public ?DateTimeImmutable $dateTime = null;
    public ?BarDto $oneToOne = null;
    public ?BarDtoCollection $oneToMany = null;
    public ?array $array = [];
}
