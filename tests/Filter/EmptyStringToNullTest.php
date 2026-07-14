<?php

declare(strict_types=1);

namespace Sunrise\Hydrator\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Sunrise\Hydrator\Filter\EmptyStringToNull;

final class EmptyStringToNullTest extends TestCase
{
    public function testEmptyString(): void
    {
        $this->assertNull((new EmptyStringToNull())(''));
    }

    public function testNotEmptyString(): void
    {
        $this->assertSame('foo', (new EmptyStringToNull())('foo'));
    }
}
