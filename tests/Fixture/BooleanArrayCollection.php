<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

use ArrayObject;
use Sunrise\Hydrator\Annotation\Subtype;
use Sunrise\Hydrator\Dictionary\BuiltinType;

final class BooleanArrayCollection extends ArrayObject
{
    public function __construct(#[Subtype(BuiltinType::BOOL)] array ...$elements)
    {
        parent::__construct($elements);
    }
}
