<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixtures;

use Sunrise\Hydrator\Annotation\Ignore;

final class ObjectWithIgnoredProperty
{
    /**
     * @Ignore()
     */
    #[Ignore]
    public string $value = 'e3097ed0-e771-4288-8f22-48c968563826';
}
