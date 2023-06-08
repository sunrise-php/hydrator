<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Format;

final class ObjectWithOptionalUnixTimeStamp
{
    /**
     * @Format("U")
     */
    #[Format('U')]
    public ?DateTimeImmutable $value = null;
}
