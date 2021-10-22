<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTime;
use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Alias;

final class Foo
{
    public static string $statical = '50f4e382-2858-4991-b045-a121004cec80';
    public ?string $nullable = 'ed0110a9-01ac-4f75-a205-223c98d2d2b5';
    public string $valuable = '5bf11aa0-08b3-4429-a6d7-4ebf6d70919c';
    public string $required;

    public bool $boolean;
    public int $integer;
    public float $number;
    public string $string;
    public array $array;
    public object $object;

    public DateTime $dateTime;
    public DateTimeImmutable $dateTimeImmutable;

    public Bar $bar;
    public BarCollection $barCollection;

    /** @Alias("non-normalized") */
    #[Alias('non-normalized')]
    public string $normalized;
}
