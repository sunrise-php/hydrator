<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Fixture;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<string>
 */
final class MyclabsEnum extends Enum
{
    private const FOO = 'cc77dcf7-9266-43c1-b434-ff37344ee466';
    private const BAR = 'ee7e1a9b-c07d-4c9a-b5f6-aed0a917a94c';
    private const BAZ = 'aee53f73-e112-44a1-a3d5-63ea2b272383';
}
