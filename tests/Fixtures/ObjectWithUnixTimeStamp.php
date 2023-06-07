<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Format;

final class ObjectWithUnixTimeStamp
{
    /**
     * @Format("U")
     */
    #[Format('U')]
    public DateTimeImmutable $value;
}
