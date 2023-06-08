<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

final class ObjectWithOptionalIntegerEnum
{
    public IntegerEnum $value = IntegerEnum::FOO;
}
