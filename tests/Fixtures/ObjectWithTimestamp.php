<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Format;

final class ObjectWithTimestamp
{
    public const FORMAT = 'Y-m-d H:i:s';

    /**
     * @Format(self::FORMAT)
     */
    #[Format(self::FORMAT)]
    public DateTimeImmutable $value;
}
